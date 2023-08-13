<?php

namespace VCD2\Applications\Service;

use Monolog\Logger;
use Nette\SmartObject;
use VCD2\Applications\Application;
use VCD2\Applications\ApplicationException;
use VCD2\Credits\Credit;
use VCD2\Orm;
use VCD2\Users\Service\Credits;

/**
 * @method onAccept($id, Application $application)
 * @method onReject($id, $reason, Application $application)
 */
class Applications {
    
    use SmartObject;

    public $onAccept = [];

    public $onReject = [];

    private $orm;
    
    private $credits;

    /** @var Logger */
    private $logger;
    
    function __construct(Orm $orm, Credits $credits, Logger $logger) {
        $this->orm = $orm;
        $this->credits = $credits;
        $this->logger = $logger->withName('vcd.applications');
    }

    /**
     * @param $id
     * @throws \VCD2\FlashMessageException
     */
    function acceptApplication($id) {
        $application = $this->fetchApplication($id);

        $application->markAccepted();
        $this->orm->persistAndFlush($application);

        $this->logger->info(sprintf('Přihláška %s byla schválena.', $application));

        $this->onAccept($id, $application);
    }
    
    function rejectApplication($id, $reason = NULL) {
        $application = $this->fetchApplication($id);
        
        $application->markRejected();
        
        // vrácení kreditů do kyblíku s nejbližší expirací nebo +x měsíců od zrušení
        if($application->creditPayment !== NULL) {
            $expiration = $application->rejectedAt->modify(Credit::EXPIRATION_APPLICATION_REJECTED_REFUND);
            $nearestExpirationCredit = $application->user->creditWithNearestExpiration;
            if($nearestExpirationCredit !== NULL && $nearestExpirationCredit->expiresAt > $expiration) {
                $expiration = $nearestExpirationCredit->expiresAt;
            }
            $this->credits->add($application->user->id, $application->creditPayment->difference, $expiration, 'Vrácení kreditu za zrušenou přihlášku #' . $id);
        }

        $this->orm->persist($application);
        $this->orm->flush();

        $this->logger->info(sprintf('Přihláška %s byla odmítnuta%s.', $application, empty($reason) ? '' : sprintf(', důvod: %s', $reason)));

        $this->onReject($id, $reason, $application);
    }

    /**
     * @param $id
     * @return NULL|Application
     * @throws ApplicationException
     */
    private function fetchApplication($id) {
        $application = $id instanceof Application ? $id : $this->orm->applications->get($id);
        if($application === NULL) {
            throw ApplicationException::create(sprintf('Application %s not found.', $id), sprintf('Přihláška %s nebyla nalezena v databázi.', $id));
        }
        return $application;
    }
    
}
