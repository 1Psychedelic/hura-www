<?php

namespace VCD2\Applications;

use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasOne;
use VCD2\Entity;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property ManyHasOne|Application $application {m:1 Application::$payments}
 * @property OneHasOne|\Hafo\GoPay\Payment|NULL $gopayPayment {1:1 \Hafo\GoPay\Payment, isMain=TRUE, oneSided=TRUE}
 * @property OneHasOne|\Hafo\Fio\Payment|NULL $fioPayment {1:1 \Hafo\Fio\Payment, isMain=TRUE, oneSided=TRUE}
 * @property string|NULL $gatewayUrl {virtual}
 * @property int $amount
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable $createdAt {default now}
 *
 *
 **** Příznaky
 * @property bool $isPaid {virtual}
 *
 *
 */
class Payment extends Entity {

    function __construct(Application $application, $amount) {
        parent::__construct();

        $this->application = $application;
        $this->amount = $amount;
    }

    protected function getterIsPaid() {
        if($this->gopayPayment !== NULL) {
            return $this->gopayPayment->state === \Hafo\GoPay\Payment::STATE_PAID;
        }
        return TRUE;
    }

    protected function getterGatewayUrl() {
        if($this->gopayPayment !== NULL && strlen($this->gopayPayment->gatewayUrl) > 0) {
            return $this->gopayPayment->gatewayUrl;
        }
        return NULL;
    }

    static function createFromGoPayPayment(Application $application, \Hafo\GoPay\Payment $payment) {
        $instance = new self($application, $payment->amount / 100);
        $instance->gopayPayment = $payment;
        return $instance;
    }

    static function createFromFioPayment(Application $application, \Hafo\Fio\Payment $payment) {
        $instance = new self($application, $payment->amount);
        $instance->fioPayment = $payment;
        return $instance;
    }

}
