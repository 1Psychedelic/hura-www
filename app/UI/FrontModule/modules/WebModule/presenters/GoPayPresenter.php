<?php

namespace VCD\UI\FrontModule\WebModule;

use Hafo\GoPay\Payment;
use Hafo\GoPay\Service\GoPay;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use VCD\UI\FrontModule\ApplicationsModule\ApplicationPresenter;

class GoPayPresenter extends BasePresenter {

    const LINK_PAYMENT_STATE_CHANGED = ':Front:Web:GoPay:paymentStateChanged';
    const LINK_TEST_GATEWAY = ':Front:Web:GoPay:testGateway';

    function actionPaymentStateChanged($id, $hash) {
        // todo
        $this->terminate();
    }

    function actionTestGateway($id) {
        $gopay = $this->container->get(GoPay::class);
        if($gopay instanceof GoPay\FakeGoPay) {
            $application = $this->orm->applications->get($id);
            foreach($application->payments as $payment) {
                if(!$payment->isPaid && $payment->gopayPayment !== NULL && !$payment->gopayPayment->hasFailed) {
                    $f = new Form;
                    $f->addRadioList('state', 'Stav platby', array_flip(Payment::STATES_MAPPING))
                        ->setDefaultValue($payment->gopayPayment->state);
                    $f->addSubmit('set', 'Nastavit');
                    $f->onSuccess[] = function(Form $f) use ($payment, $gopay, $application) {
                        if($f->isSubmitted() === $f['set']) {
                            $gopay->setState($payment->gopayPayment->gopayId, $f->getValues(TRUE)['state']);
                            $this->redirect(ApplicationPresenter::LINK_RETURN_FROM_GATEWAY, ['_event' => $application->event->slug]);
                        }
                    };
                    $this->addComponent($f, 'form');
                    $this->template->payment = $payment;
                    return;
                }
            }
        }
        throw new BadRequestException;
    }

}
