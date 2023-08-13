<?php

namespace VCD\Admin\Payments\UI;

use Hafo\Fio\FioException;
use Hafo\Fio\Payment;
use Hafo\NetteBridge\Forms\FormFactory;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Paginator;
use Nextras\Orm\Collection\ICollection;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;
use VCD2\Applications\Service\Fio;
use VCD2\Orm;

class FioPaymentsControl extends Control {

    const ITEMS_PER_PAGE = 50;

    private $container;

    function __construct(ContainerInterface $container, $page = 1) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($page) {

            $paginator = new Paginator;

            $cond = [
                'amount>' => 0,
            ];

            $orm = $this->container->get(Orm::class);
            $payments = $orm->fioPayments->findBy($cond)->orderBy('id', ICollection::DESC);

            $paginator->setItemCount($payments->count());
            $paginator->setItemsPerPage(self::ITEMS_PER_PAGE);
            $paginator->setPage($page);

            $payments = $payments->limitBy($paginator->getLength(), $paginator->getOffset());
            /** @var Payment[] $payments */

            $byFioId = [];
            foreach($payments as $payment) {
                $byFioId[$payment->fioId] = NULL;
            }

            $systemPayments = $orm->payments->findBy(['this->fioPayment->fioId' => array_keys($byFioId)]);
            foreach($systemPayments as $systemPayment) {
                $byFioId[$systemPayment->fioPayment->fioId] = $systemPayment;
            }

            foreach($payments as $payment) {
                if($byFioId[$payment->fioId] === NULL) {
                    $f = $this->container->get(FormFactory::class)->create();
                    $f->addText('application', '')->setOption('placeholder', 'ID přihlášky');
                    $f->addSubmit('pair', 'Spárovat s přihláškou');
                    $f->onSuccess[] = function(Form $f) use ($payment, $orm) {
                        if($f->isSubmitted() === $f['pair']) {
                            $data = $f->getValues(TRUE);
                            $application = $orm->applications->get($data['application']);
                            if($application === NULL) {
                                $this->presenter->flashMessage('Přihláška s tímto ID neexistuje.', 'danger');
                                $this->presenter->redirect('this');
                            }

                            $this->container->get(Fio::class)->manualPair($application, $payment);
                            $this->presenter->flashMessage('Platba byla spárována s přihláškou.', 'success');
                            $this->presenter->redirect('this');
                        }
                    };
                    $this->addComponent($f, 'pair' . $payment->fioId);
                }
            }

            $this->template->paginator = $paginator;
            $this->template->list = $payments;
            $this->template->byFioId = $byFioId;
        };
    }
    
    function handleRefresh() {
        try {
            $result = $this->container->get(Fio::class)->pairPaymentsToApplications();

            $this->presenter->flashMessage(sprintf('Bylo načteno %s nových plateb, %s z nich bylo přiřazeno k přihláškám.', array_sum($result), $result[Fio::RESULT_PAIRED]), 'success');
            $this->presenter->redirect('this');
        } catch (FioException $e) {
            $this->presenter->flashMessage('Došlo k chybě při načítání plateb z Fio API.', 'danger');
            $this->presenter->redirect('this');
        }
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
