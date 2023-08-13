<?php

namespace Hafo\GoPay\Service\GoPay;

use GoPay\Definition\Payment\BankSwiftCode;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;
use GoPay\Payments;
use Hafo\GoPay\GoPayException;
use Hafo\GoPay\Payment;
use Hafo\GoPay\Service\GoPay;
use Nette\Application\LinkGenerator;
use Nette\Http\Session;
use Nette\Utils\Random;
use VCD\UI\FrontModule\WebModule\GoPayPresenter;

class FakeGoPay implements GoPay {

    private $payments;

    private $linkGenerator;

    function __construct(Session $session, LinkGenerator $linkGenerator) {
        $session = $session->getSection(get_class($this));
        if(!isset($session['payments']) || !is_array($session['payments'])) {
            $session['payments'] = [];
        }

        $this->payments = $session;
        $this->linkGenerator = $linkGenerator;
    }

    function createPayment(array $request) {
        $id = intval(Random::generate(8, '0-9'));
        $payment = new Payment($id, $request['amount'], $request['currency'], 'CREATED');
        $payment->gatewayUrl = $this->linkGenerator->link(substr(GoPayPresenter::LINK_TEST_GATEWAY, 1), ['id' => $request['order_number']]);
        $this->setState($payment->gopayId, $payment->state);
        return $payment;
    }

    function refreshStatus(Payment $payment) {
        if(!isset($this->payments[$payment->gopayId])) {
            throw new GoPayException('Payment not found.');
        }
        $payment->state = $this->payments[$payment->gopayId];
    }

    function refundPayment(Payment $payment, $amount = self::AMOUNT_ALL) {
        if(!isset($this->payments[$payment->gopayId])) {
            throw new GoPayException('Payment not found.');
        }
        $this->setState($payment->gopayId, 'REFUNDED');
    }

    function setState($id, $state) {
        $this->payments[$id] = $state;
    }

    function getPaymentInstruments($goid = NULL, $currency = Currency::CZECH_CROWNS) {
        return [
            'groups' => [
                'card-payment' => [
                    'label' => [
                        'cs' => 'Platební karta',
                    ],
                ],
                'bank-transfer' => [
                    'label' => [
                        'cs' => 'Rychlý bankovní převod',
                    ],
                ],
            ],
            'enabledPaymentInstruments' => [
                [
                    'paymentInstrument' => PaymentInstrument::PAYMENT_CARD,
                    'label' => [
                        'cs' => 'Platební karta',
                    ],
                    'image' => [
                        'normal' => 'https://gate.gopay.cz/images/checkout/payment_card.png',
                        'large' => 'https://gate.gopay.cz/images/checkout/payment_card@2x.png',
                    ],
                    'group' => 'card-payment',
                    'enabledSwifts' => NULL,
                ],
                [
                    'paymentInstrument' => PaymentInstrument::BANK_ACCOUNT,
                    'label' => [
                        'cs' => 'Rychlý bankovní převod',
                    ],
                    'image' => [
                        'normal' => 'https://gate.gopay.cz/images/checkout/bank_account.png',
                        'large' => 'https://gate.gopay.cz/images/checkout/bank_account@2x.png',
                    ],
                    'group' => 'bank-transfer',
                    'enabledSwifts' => [
                        [
                            'swift' => 'GIBACZPX',
                            'label' => [
                                'cs' => 'Platba 24',
                            ],
                            'image' => [
                                'normal' => 'https://gate.gopay.cz/images/checkout/GIBACZPX.png',
                                'large' => 'https://gate.gopay.cz/images/checkout/GIBACZPX@2x.png',
                            ],
                            'isOnline' => TRUE,
                        ],
                    ],
                ],
                [
                    'paymentInstrument' => PaymentInstrument::BITCOIN,
                    'label' => [
                        'cs' => 'Platba Bitcoiny',
                    ],
                    'image' => [
                        'normal' => 'https://gate.gopay.cz/images/checkout/bitcoin.png',
                        'large' => 'https://gate.gopay.cz/images/checkout/bitcoin@2x.png',
                    ],
                ]
            ],
        ];
    }

}
