<?php

namespace Hafo\GoPay;

use GoPay\Definition\Payment\Currency;
use Hafo\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property int $gopayId
 * @property int $amount
 * @property string $currency {enum Currency::*}
 * @property int $state {enum self::STATE_*}
 * @property int|NULL $paymentInstrument {enum self::PAYMENT_INSTRUMENT_*}
 * @property string|NULL $gatewayUrl
 *
 * @property bool $hasFailed {virtual}
 */
class Payment extends Entity {

    const STATE_CREATED = 0;
    const STATE_PAYMENT_METHOD_CHOSEN = 1;
    const STATE_PAID = 2;
    const STATE_AUTHORIZED = 3;
    const STATE_CANCELED = 4;
    const STATE_TIMEOUTED = 5;
    const STATE_REFUNDED = 6;
    const STATE_PARTIALLY_REFUNDED = 7;

    const STATES_MAPPING = [
        'CREATED' => self::STATE_CREATED,
        'PAYMENT_METHOD_CHOSEN' => self::STATE_PAYMENT_METHOD_CHOSEN,
        'PAID' => self::STATE_PAID,
        'AUTHORIZED' => self::STATE_AUTHORIZED,
        'CANCELED' => self::STATE_CANCELED,
        'TIMEOUTED' => self::STATE_TIMEOUTED,
        'REFUNDED' => self::STATE_REFUNDED,
        'PARTIALLY_REFUNDED' => self::STATE_PARTIALLY_REFUNDED,
    ];

    const STATES_INIT = [
        self::STATE_CREATED,
        self::STATE_PAYMENT_METHOD_CHOSEN,
    ];
    const STATES_FAIL = [
        self::STATE_CANCELED,
        self::STATE_TIMEOUTED,
    ];

    const PAYMENT_INSTRUMENT_PAYMENT_CARD = 0;
    const PAYMENT_INSTRUMENT_BANK_ACCOUNT = 1;
    const PAYMENT_INSTRUMENT_PRSMS = 2;
    const PAYMENT_INSTRUMENT_MPAYMENT = 3;
    const PAYMENT_INSTRUMENT_PAYSAFECARD = 4;
    const PAYMENT_INSTRUMENT_SUPERCASH = 5;
    const PAYMENT_INSTRUMENT_GOPAY = 6;
    const PAYMENT_INSTRUMENT_PAYPAL = 7;
    const PAYMENT_INSTRUMENT_BITCOIN = 8;

    const PAYMENT_INSTRUMENTS_MAPPING = [
        'PAYMENT_CARD' => self::PAYMENT_INSTRUMENT_PAYMENT_CARD,
        'BANK_ACCOUNT' => self::PAYMENT_INSTRUMENT_BANK_ACCOUNT,
        'PRSMS' => self::PAYMENT_INSTRUMENT_PRSMS,
        'MPAYMENT' => self::PAYMENT_INSTRUMENT_MPAYMENT,
        'PAYSAFECARD' => self::PAYMENT_INSTRUMENT_PAYSAFECARD,
        'SUPERCASH' => self::PAYMENT_INSTRUMENT_SUPERCASH,
        'GOPAY' => self::PAYMENT_INSTRUMENT_GOPAY,
        'PAYPAL' => self::PAYMENT_INSTRUMENT_PAYPAL,
        'BITCOIN' => self::PAYMENT_INSTRUMENT_BITCOIN,
    ];


    public function __construct($gopayId, $amount, $currency = Currency::CZECH_CROWNS, $state = self::STATE_CREATED) {
        parent::__construct();

        $this->gopayId = $gopayId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->state = $state;
    }

    function updateFromArray(array $data) {
        $this->state = $data['state'];
        if(isset($data['gw_url'])) {
            $this->gatewayUrl = $data['gw_url'];
        }
    }

    protected function setterState($value) {
        return $this->mapState($value);
    }

    protected function setterPaymentInstrument($value) {
        return $this->mapPaymentInstrument($value);
    }

    protected function getterHasFailed() {
        return in_array($this->state, self::STATES_FAIL, TRUE);
    }

    protected function getterIsNew() {
        return in_array($this->state, self::STATES_INIT, TRUE);
    }

    private function mapState($state) {
        if(array_key_exists($state, self::STATES_MAPPING)) {
            $state = self::STATES_MAPPING[$state];
        }
        return $state;
    }

    private function mapPaymentInstrument($paymentInstrument) {
        if(array_key_exists($paymentInstrument, self::PAYMENT_INSTRUMENTS_MAPPING)) {
            $paymentInstrument = self::PAYMENT_INSTRUMENTS_MAPPING[$paymentInstrument];
        }
        return $paymentInstrument;
    }

    static function createFromArray(array $data) {
        $payment = new self($data['id'], $data['amount'], $data['currency'], $data['state']);
        if(isset($data['gw_url'])) {
            $payment->gatewayUrl = $data['gw_url'];
        }
        return $payment;
    }

}
