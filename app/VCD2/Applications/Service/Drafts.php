<?php

namespace VCD2\Applications\Service;

use Monolog\Logger;
use Nette\Http\Session;
use Nette\SmartObject;
use Nextras\Dbal\Connection;
use Throwable;
use VCD2\Applications\Application;
use VCD2\Applications\ApplicationCapacityException;
use VCD2\Applications\ApplicationClosedException;
use VCD2\Applications\ApplicationException;
use VCD2\Discounts\DiscountCodeException;
use VCD2\Discounts\DiscountException;
use VCD2\Events\Event;
use VCD2\Orm;
use VCD2\Users\InsufficientCreditException;
use VCD2\Users\Service\Credits;
use VCD2\Users\Service\UserContext;

/**
 * @method onFinish($id, $hash, Application $application)
 */
class Drafts {

    use SmartObject;

    const SAVEPOINT_FINISH_DRAFT = 'finishDraft';

    public $onFinish = [];

    private $orm;

    private $connection;

    private $user;

    private $credits;

    private $gopay;

    private $session;

    /**
     * @var Logger
     */
    private $logger;

    function __construct(Orm $orm, Connection $connection, UserContext $user, Credits $credits, GoPay $gopay,  Session $session, Logger $logger) {
        $this->orm = $orm;
        $this->connection = $connection;
        $this->user = $user->getEntity();
        $this->credits = $credits;
        $this->gopay = $gopay;
        $this->session = $session->getSection(get_class($this));
        $this->logger = $logger->withName('vcd.drafts');
    }
    
    function openDraftForEvent(Event $event) {
        $draft = NULL;
        $condition = [
            'event' => $event,
            'user' => $this->user,
            'appliedAt' => NULL,
            'acceptedAt' => NULL,
            'canceledAt' => NULL,
            'rejectedAt' => NULL,
        ];

        if($this->user === NULL) {
            $id = isset($this->session[$event->id]) ? $this->session[$event->id] : NULL;
            $draft = $id === NULL ? NULL : $this->orm->applications->getBy(array_merge($condition, ['id' => $id]));
        } else {
            $draft = $this->orm->applications->getBy($condition);
        }

        if($draft === NULL) {
            $draft = $this->createDraft($event);
        } else {
            $this->logger->info(sprintf('Otevírám načatou přihlášku %s', $draft));
        }

        if(!$this->validateDraft($draft)) {
            $draft = $this->createDraft($event);
        }

        if($this->user === NULL && isset($this->session[$event->id]) && (!$draft->isPersisted() || $this->session[$event->id] !== $draft->id)) {
            unset($this->session[$event->id]);
        }

        return $draft;
    }

    function saveDraft(Application $application) {
        $isNew = !$application->isPersisted();

        $application->refreshDiscount();
        $application->recalculatePrice();

        $this->orm->persistAndFlush($application);

        if($isNew) {
            $this->logger->info(sprintf('Do databáze byla uložena nová přihláška %s', $application));
        } else {
            $this->logger->info(sprintf('V databázi byla aktualizována přihláška %s', $application));
        }

        if($this->user === NULL) {
            $this->session[$application->event->id] = $application->id;
        }

        return $application;
    }

    function validateDraftBeforeFinish(Application $application) {
        $application->assertCanBeApplied();
    }

    /**
     * @param Application $application
     * @throws ApplicationException
     * @throws DiscountCodeException
     * @throws DiscountException
     */
    function finishDraft(Application $application) {
        $this->connection->transactional(function() use ($application) {

            $countChildren = $application->children->count();

            $this->validateDraftBeforeFinish($application);

            try {
                $application->markApplied(); // all magic is here
            } catch (Throwable $e) {
                $this->orm->persistAndFlush($application);
                $this->logger->error(
                    sprintf('Došlo k výjimce %s se zprávou "%s" při pokusu o odeslání přihlášky %s.', get_class($e), $e->getMessage(), $application),
                    ['exception' => $e]
                );
                throw $e;
            }

            if($application->isVip) {
                $this->logger->info(sprintf('Na přihlášku %s byla uplatněna věrnostní sleva.', $application));
            }

            if($application->discount !== NULL) {
                $this->logger->info(sprintf('Na přihlášku %s byla uplatněna slevová akce %s.', $application, $application->discount));
            }

            if($application->discountCode !== NULL) {
                $this->logger->info(sprintf('Byl zkonzumován slevový kód %s během odesílání přihlášky %s', $application->discountCode, $application));
            }

            if($application->isReserve) {
                $this->logger->info(sprintf('Přihláška %s byla přiřazena mezi náhradníky během odesílání.', $application));
            }

            if($application->event->siblingDiscount && $countChildren > 1) {
                $this->logger->info(sprintf('Na přihlášku %s byla uplatněna sourozenecká sleva ve výši %s Kč.', $application, $application->event->siblingDiscountValueFor($countChildren)));
            }

            if($application->isPayingByCredit && $this->user !== NULL && $this->user->creditBalance > 0) {
                $amount = $application->priceWithoutCredit < $this->user->creditBalance ? $application->priceWithoutCredit : $this->user->creditBalance;
                $this->logger->info(sprintf('Platím přihlášku %s částkou %s Kč kreditem.', $application, $amount));

                try {
                    $this->credits->spend($this->user->id, $amount, sprintf('Platba přihlášky %s kreditem.', $application), $application->id);
                } catch (InsufficientCreditException $e) {
                    throw ApplicationException::create(
                        'Insufficient credit.',
                        'Došlo k chybě při pokusu o provedení platby kreditem.'
                    );
                }
            }

            $this->orm->persistAndFlush($application);

            $this->logger->info(sprintf('Odesílání přihlášky %s proběhlo úspěšně.', $application));
        });

        $this->onFinish($application->id, $application->hash, $application);
    }

    private function validateDraft(Application $application) {
        if(!$application->isDraft) {
            $this->logger->error(sprintf('Přihláška %s už byla odeslána!', $application));
            return FALSE;
        }
        if($application->user !== $this->user) {
            $this->logger->error(sprintf('Přihláška %s nepatří uživateli %s!', $application, $this->user));
            return FALSE;
        }
        return TRUE;
    }

    private function createDraft(Event $event) {
        $this->logger->info(sprintf('Vytvářím novou přihlášku pro událost %s', $event));
        return $event->createApplication($this->user);
    }

}
