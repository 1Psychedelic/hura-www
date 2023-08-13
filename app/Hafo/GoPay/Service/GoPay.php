<?php

namespace Hafo\GoPay\Service;

use GoPay\Definition\Payment\Currency;
use Hafo\GoPay\GoPayException;
use Hafo\GoPay\Payment;

interface GoPay {

    const AMOUNT_ALL = NULL;

    /**
     * @param array $request
     * @return Payment
     * @throws GoPayException
     */
    function createPayment(array $request);

    /**
     * @param Payment $payment
     * @throws GoPayException
     */
    function refreshStatus(Payment $payment);

    /**
     * @param Payment $payment
     * @param int|NULL $amount
     * @throws GoPayException
     */
    function refundPayment(Payment $payment, $amount = self::AMOUNT_ALL);

    /**
     * @param null $goid
     * @param string $currency
     * @return array
     */
    function getPaymentInstruments($goid = NULL, $currency = Currency::CZECH_CROWNS);

}
