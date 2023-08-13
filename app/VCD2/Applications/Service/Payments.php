<?php

namespace VCD2\Applications\Service;

use Monolog\Logger;
use Nette\SmartObject;
use VCD2\Applications\Application;
use VCD2\Applications\Payment;
use VCD2\Orm;

/**
 * @method onReceivePayment(Payment $payment)
 */
class Payments
{
    use SmartObject;

    const AMOUNT_ALL = null;

    public $onReceivePayment = [];

    private $orm;

    /** @var Logger */
    private $logger;

    public function __construct(Orm $orm, Logger $logger)
    {
        $this->orm = $orm;
        $this->logger = $logger->withName('vcd.payments');
    }

    public function receivePayment(Application $application, $amount = self::AMOUNT_ALL)
    {
        $wasPaid = $application->isPaid;

        $amount = $amount === self::AMOUNT_ALL ? ($application->price - $application->paid) : $amount;
        $this->logger->info(sprintf('Byla přijata platba %s Kč za přihlášku %s', $amount, $application));

        $payment = new Payment($application, $amount);
        $this->orm->persistAndFlush($payment);

        // Poznamenat čas zaplacení
        $application->updatePaidAt();
        $this->orm->persistAndFlush($application);

        // Označit fakturu jako zaplacenou
        if ($application->isPaid && $application->invoice !== null) {
            $application->invoice->isPaid = true;
            $this->orm->persistAndFlush($application->invoice);
        }

        if (!$wasPaid && $application->isPaid) {
            $this->logger->info(sprintf('Přihláška %s je nyní plně zaplacena.', $application));
        }

        $this->onReceivePayment($payment);

        return $payment;
    }
}
