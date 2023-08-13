<?php

namespace VCD2\Applications\Service;

use Hafo\GoPay\GoPayException;
use Hafo\GoPay\Payment as GoPayPayment;
use Monolog\Logger;
use Nette\Application\LinkGenerator;
use Nette\Utils\Html;
use VCD\UI\FrontModule\ApplicationsModule\ApplicationPresenter;
use VCD\UI\FrontModule\WebModule\GoPayPresenter;
use VCD2\Applications\Application;
use VCD2\Applications\Payment;
use VCD2\Applications\PaymentException;
use VCD2\Applications\Service\Payments;
use VCD2\Orm;

class GoPay {

    private $orm;

    private $gopay;

    private $payments;

    private $linkGenerator;

    /** @var Logger */
    private $logger;

    function __construct(Orm $orm, \Hafo\GoPay\Service\GoPay $gopay, Payments $payments, LinkGenerator $linkGenerator, Logger $logger) {
        $this->orm = $orm;
        $this->gopay = $gopay;
        $this->payments = $payments;
        $this->linkGenerator = $linkGenerator;
        $this->logger = $logger->withName('vcd.gopay');
    }

    /**
     * @param Application $application
     * @return Payment
     * @throws PaymentException
     */
    function createPayment(Application $application) {
        if($application->isPaid) {
            $this->logger->info(sprintf('Byl detekován pokus o vytvoření GoPay platby pro již zaplacenou přihlášku %s.', $application));
            throw PaymentException::create(
                'Unable to init a payment gateway - application has already been paid.',
                'Nemohu vyvolat platební bránu - přihláška již byla v minulosti zaplacená.'
            );
        }

        if(!$application->paymentMethod->isGopay) {
            $this->logger->info(sprintf('Byl detekován pokus o vytvoření GoPay platby pro přihlášku %s, která nemá zvolenou GoPay platební metodu.', $application));
            throw PaymentException::create(
                'Application doesn\'t have GoPay payment method.',
                'Nemohu vyvolat platební bránu - přihláška nemá nastavenou platební metodu přes GoPay.'
            );
        }

        $amount = $application->price - $application->paid;
        if($application->payOnlyDeposit && $application->paid < $application->deposit) {
            $amount = $application->deposit - $application->paid;
        }

        $request = [
            'amount' => $amount * 100, // halíře
            'currency' => 'CZK',
            'order_number' => (string)$application->id,
            'items' => $this->buildItems($application),
            'payer' => [
                'allowed_payment_instruments' => [$application->paymentMethod->gopayPaymentInstrument],
                'default_payment_instrument' => $application->paymentMethod->gopayPaymentInstrument,
            ],
            'callback' => [
                'return_url' => $this->linkGenerator->link(substr(ApplicationPresenter::LINK_RETURN_FROM_GATEWAY, 1), ['_event' => $application->event->slug]),
                'notification_url' => $this->linkGenerator->link(substr(GoPayPresenter::LINK_PAYMENT_STATE_CHANGED, 1), ['id' => $application->id, $application->hash]),
            ],
        ];

        try {
            $gopayPayment = $this->gopay->createPayment($request);
            $payment = Payment::createFromGoPayPayment($application, $gopayPayment);

            $this->orm->persist($gopayPayment);
            $this->orm->persist($payment);
            $this->orm->flush();

            $this->logger->info(sprintf('Byla vytvořena GoPay platba pro přihlášku %s.', $application));

            return $payment;
        } catch (GoPayException $e) {
            $this->logger->error(sprintf('Došlo k chybě při pokusu o vyvolání platební brány %s pro přihlášku %s.', ($e->getPrevious() === NULL ? '' : '(' . $e->getCode() . ')'), $application));
            throw new PaymentException('Unable to init a payment gateway.', 0, $e);
        }
    }

    /**
     * @param Application $application
     * @return bool TRUE if everything went fine, FALSE if any request failed
     */
    function refreshStatus(Application $application) {
        $wasPaid = $application->isPaid;

        $fails = 0;
        foreach($application->payments as $payment) {
            $gopayPayment = $payment->gopayPayment;
            if($gopayPayment === NULL) {
                continue;
            }
            try {
                $this->gopay->refreshStatus($gopayPayment);
                $this->orm->persist($gopayPayment);
                $this->logger->info(sprintf('Byl aktualizován stav GoPay platby %s pro přihlášku %s.', $gopayPayment->gopayId, $application));
            } catch (GoPayException $e) {
                $this->logger->error(sprintf('Nepodařilo se aktualizovat stav GoPay platby %s pro přihlášku %s %s.', $gopayPayment->gopayId, $application, ($e->getPrevious() === NULL ? '' : '(' . $e->getCode() . ')')));
                $fails++;
            }
        }

        $this->orm->flush();
        $this->orm->refreshAll();

        if(!$wasPaid && $application->isPaid) {
            $this->logger->info(sprintf('Přihláška %s je nyní plně zaplacená.', $application));
        }

        return $fails === 0;
    }

    function getPaymentInstrumentsForRadioList() {
        $data = [];
        $response = $this->gopay->getPaymentInstruments();
        foreach($response['enabledPaymentInstruments'] as $instrument) {
            $data[$instrument['paymentInstrument']] = Html::el()->setHtml('<strong>' . $instrument['label']['cs'] . '</strong><br><img src="' . $instrument['image']['normal'] . '" style="margin-top:10px">');
        }
        return $data;
    }

    private function buildItems(Application $application) {
        $items = [];
        
        foreach($application->createInvoiceItems() as $invoiceItem) {
            $items[] = [
                'type' => $invoiceItem->totalPrice < 0 ? 'DISCOUNT' : 'ITEM',
                'count' => $invoiceItem->amount,
                'name' => $invoiceItem->name,
                'amount' => $invoiceItem->totalPrice * 100, // halíře
            ];
        }
        
        return $items;
    }

}
