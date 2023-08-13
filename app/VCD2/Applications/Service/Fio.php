<?php

namespace VCD2\Applications\Service;

use Monolog\Logger;
use VCD2\Applications\Application;
use VCD2\Applications\Payment;
use VCD2\Orm;

class Fio {

    const DEFAULT_SINCE = '-1 month';

    const RESULT_PAIRED = 'paired';
    const RESULT_UNPAIRED = 'unpaired';

    private $fio;

    private $orm;

    private $payments;

    /** @var Logger */
    private $logger;

    function __construct(\Hafo\Fio\Service\Fio $fio, Orm $orm, Payments $payments, Logger $logger) {
        $this->fio = $fio;
        $this->orm = $orm;
        $this->payments = $payments;
        $this->logger = $logger->withName('vcd.fio');
    }

    function pairPaymentsToApplications(\DateTimeInterface $since = NULL) {
        $since = $since === NULL ? new \DateTimeImmutable(self::DEFAULT_SINCE) : $since;

        $paired = 0;
        $unpaired = 0;

        $newPayments = $this->fio->getTransactionsByPeriod($since, new \DateTimeImmutable('now'));
        $createdPayments = [];
        foreach($newPayments as $newPayment) {
            if($newPayment->variableSymbol === NULL) {
                $unpaired++;
                continue;
            }

            /** @var Application|NULL $application */
            $application = $this->orm->applications->get($newPayment->variableSymbol);
            if($application === NULL) {
                $this->logger->info(sprintf('Byla přijata platba s variabilním symbolem %s, ale přihláška s tímto ID v databázi neexistuje.', $newPayment->variableSymbol));
                $unpaired++;
                continue;
            }

            $payment = Payment::createFromFioPayment($application, $newPayment);
            $createdPayments[] = $payment;

            $this->logger->info(sprintf('Byla přijata Fio platba s variabilním symbolem %s a přiřazena k příslušné přihlášce.', $newPayment->variableSymbol));
            $this->orm->persist($payment);

            $paired++;
        }

        $this->orm->flush();

        // fire event
        foreach($createdPayments as $createdPayment) {
            $this->payments->onReceivePayment($createdPayment);
        }

        return [
            self::RESULT_PAIRED => $paired,
            self::RESULT_UNPAIRED => $unpaired,
        ];
    }

    function manualPair(Application $application, \Hafo\Fio\Payment $fioPayment) {
        $payment = Payment::createFromFioPayment($application, $fioPayment);
        $this->orm->persistAndFlush($payment);

        $this->logger->info(sprintf('Fio platba s Fio ID %s byla ručně přiřazena k přihlášce %s.', $fioPayment->fioId, $application));

        // fire event
        $this->payments->onReceivePayment($payment);
    }

}
