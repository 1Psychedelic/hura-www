<?php

namespace Hafo\GoPay\Service\GoPay;

use GoPay\Definition\Payment\Currency;
use GoPay\Payments;
use Hafo\GoPay\GoPayException;
use Hafo\GoPay\Payment;

class GoPay implements \Hafo\GoPay\Service\GoPay {

    private $payments;

    function __construct(Payments $payments) {
        $this->payments = $payments;
    }

    function createPayment(array $request) {
        $response = $this->payments->createPayment($request);
        if($response->hasSucceed()) {
            return Payment::createFromArray($response->json);
        } else {
            throw new GoPayException(sprintf('GoPay returned status code %s.', $response->statusCode), $response->statusCode);
        }
    }

    function refreshStatus(Payment $payment) {
        $response = $this->payments->getStatus($payment->gopayId);
        if($response->hasSucceed()) {
            $payment->updateFromArray($response->json);
        } else {
            throw new GoPayException(sprintf('GoPay returned status code %s.', $response->statusCode), $response->statusCode);
        }
    }

    function refundPayment(Payment $payment, $amount = self::AMOUNT_ALL) {
        $response = $this->payments->refundPayment($payment->gopayId, $amount === self::AMOUNT_ALL ? $payment->amount : $amount);
        if(!$response->hasSucceed()) {
            throw new GoPayException(sprintf('GoPay returned status code %s.', $response->statusCode), $response->statusCode);
        }
    }

    function getPaymentInstruments($goid = NULL, $currency = Currency::CZECH_CROWNS) {
        $goid = $goid === NULL ? $this->payments->gopay->getConfig('goid') : $goid;
        $response = $this->payments->getPaymentInstruments($goid, $currency);
        if(!$response->hasSucceed()) {
            throw new GoPayException(sprintf('GoPay returned status code %s.', $response->statusCode), $response->statusCode);
        }
        return $response->json;
    }

}
