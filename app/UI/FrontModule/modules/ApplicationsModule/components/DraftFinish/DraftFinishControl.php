<?php

namespace VCD\UI\FrontModule\ApplicationsModule;

use GoPay\Definition\Payment\PaymentInstrument;
use Hafo\DI\Container;
use Hafo\Google\ConversionTracking\Tracker;
use VCD2\Applications\Service\GoPay;
use Hafo\NetteBridge\Forms\FormFactory;
use Monolog\Logger;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use VCD\UI\FrontModule\UserModule\ApplicationPresenter as UserApplicationPresenter;
use VCD2\Applications\Application;
use VCD2\Applications\ApplicationCapacityException;
use VCD2\Applications\ApplicationClosedException;
use VCD2\Applications\Service\Drafts;
use VCD2\Discounts\DiscountCodeException;
use VCD2\Discounts\DiscountException;
use VCD2\Discounts\Service\DiscountCodes;
use VCD2\FlashMessageException;
use VCD2\Orm;
use VCD2\Users\Service\UserContext;

class DraftFinishControl extends Control {

    public $onSave = [];

    private $draft;

    private $user;

    private $gopay;

    private $drafts;

    private $orm;
    
    /** @var Logger */
    private $logger;

    function __construct(Container $container, Application $draft) {

        $this->draft = $draft;
        $this->user = $container->get(UserContext::class)->getEntity();
        $this->gopay = $container->get(GoPay::class);

        $formFactory = $container->get(FormFactory::class);
        $this->orm = $container->get(Orm::class);
        $discountCodes = $container->get(DiscountCodes::class);
        $this->logger = $logger = $container->get(Logger::class)->withName('vcd.ui.draftfinish');
        $this->drafts = $drafts = $container->get(Drafts::class);

        $this->onAnchor[] = function() use ($draft, $formFactory, $discountCodes, $logger, $drafts) {

            // just to be sure...
            $draft->refreshDiscount();
            $draft->recalculatePrice();

            if($draft->canUseDiscountCode) {

                $f = $formFactory->create();
                $f->addText('discount', 'Máte slevový kód? Zadejte ho sem:')->setRequired(FALSE)->addFilter(function($val) {return strtoupper($val);});
                $f->addSubmit('save', 'Použít slevový kód');
                $f->onSuccess[] = function(Form $f) use ($draft, $discountCodes, $logger, $drafts) {
                    if($f->isSubmitted() === $f['save']) {
                        $code = $f->getValues(TRUE)['discount'];

                        if(strlen($code) === 0) {
                            if($draft->discountCode !== NULL) {
                                $logger->info(sprintf('Odebírám slevový kód z přihlášky %s', (string)$draft));
                                $draft->resetDiscountCode();
                                $this->parent->flashMessage('Slevový kód byl odebrán z přihlášky.', 'success');
                            }
                        } else {
                            $discount = $discountCodes->getUsableCodeForApplication($draft, $code);
                            if($discount === NULL) {
                                $draft->resetDiscountCode();
                                $logger->notice(sprintf('Slevový kód %s neexistuje nebo nejde použít pro přihlášku %s', $code, (string)$draft));
                                $this->parent->flashMessage('Zadaný slevový kód neexistuje nebo mu vypršela platnost.', 'danger');
                            } else {
                                try {
                                    $draft->applyDiscountCode($discount);
                                    $logger->info(sprintf('Aplikuji slevový kód %s do přihlášky %s', $code, (string)$draft));
                                    $this->parent->flashMessage('Slevový kód byl přijat.', 'success');
                                } catch (DiscountCodeException $e) {
                                    $logger->notice(sprintf('Slevový kód %s neexistuje nebo nejde použít pro přihlášku %s', $code, (string)$draft));
                                    $this->parent->flashMessage('Zadaný slevový kód neexistuje nebo mu vypršela platnost.', 'danger');
                                }
                            }
                        }
                        $drafts->saveDraft($draft);
                        $this->presenter->redirect('this#tabs');
                    }
                };

                $f->setValues(['discount' => $draft->discountCode === NULL ? NULL : $draft->discountCode->code]);
                $this->addComponent($f, 'discount');
            }

            $f = $formFactory->create();
            $paymentMethods = $this->buildPaymentMethods();
            $this->template->showPaymentForm = $showPaymentForm = $paymentMethods !== NULL && count($paymentMethods) > 1;
            if($showPaymentForm) {
                $f->addRadioList('paymentMethod', '', $paymentMethods)
                    ->setRequired('Vyberte prosím způsob platby.');
                $f->addRadioList('payDeposit', '', [
                    0 => 'Zaplatím ihned celou částku',
                    1 => Html::el()->setHtml('Teď zaplatím zálohu,<br>zbytek později'),
                ])->setRequired('Vyberte prosím zda chcete teď zaplatit celou částku nebo jen zálohu.');
            }
            $f->addButtonSubmit('finish', Html::el()->addHtml(Html::el('span')->class('glyphicon glyphicon-ok'))->addHtml(' Dokončit rezervaci'));
            $f->onValidate[] = function(Form $f) use ($paymentMethods) {
                $data = $f->getValues(TRUE);
                $paymentMethod = isset($data['paymentMethod']) ? $data['paymentMethod'] : NULL;
                if($paymentMethod === NULL && $paymentMethods !== NULL && count($paymentMethods) === 1) {
                    $keys = array_keys($paymentMethods);
                    $paymentMethod = array_shift($keys);
                }
                if($paymentMethod !== NULL) {
                    $paymentMethod = $this->orm->paymentMethods->get($paymentMethod);
                    if($paymentMethod === NULL || !$paymentMethod->isEnabled) {
                        $f['paymentMethod']->addError('Vybraná platební metoda již není k dispozici, vyberte prosím jiný způsob platby.');
                    }
                    if($this->user !== NULL && $paymentMethod !== NULL) {
                        $this->user->paymentMethod = $paymentMethod;
                        $this->user->payOnlyDeposit = isset($data['payDeposit']) ? $data['payDeposit'] : FALSE;
                        $this->orm->persist($this->user);
                    }
                    $this->draft->paymentMethod = $paymentMethod;
                    $this->draft->payOnlyDeposit = isset($data['payDeposit']) ? $data['payDeposit'] : FALSE;
                    $this->drafts->saveDraft($this->draft);
                }
            };
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['finish']) {
                    if($this->draft->price === 0 || !$this->draft->paymentMethod->isGopay) {
                        $this->executeWithValidation(function () {
                            $this->drafts->finishDraft($this->draft);

                            $this->logger->info(sprintf('Odesílám přihlášku %s bez přechodu na platební bránu - částka k zaplacení je nula.', $this->draft));
                            $this->presenter->redirect(UserApplicationPresenter::LINK_DEFAULT, ['id' => $this->draft->id, 'hash' => $this->draft->hash]);
                        });
                    } else {
                        $this->executeWithValidation(function () {
                            $this->drafts->validateDraftBeforeFinish($this->draft);
                            $payment = $this->gopay->createPayment($this->draft);
                            if($payment->gatewayUrl !== NULL) {
                                $this->logger->info(sprintf('Probíhá přesměrování na platební bránu pro přihlášku %s.', $this->draft));
                                $this->presenter->redirectUrl($payment->gatewayUrl);
                            } else {
                                $this->presenter->flashMessage('Došlo k chybě při pokusu o kontaktování platební brány. Zkuste to prosím znovu později.', 'danger');
                                $this->presenter->redirect('this');
                            }
                        });
                    }
                }
            };
            if($paymentMethods !== NULL) {
                $paymentMethod = NULL;
                if($this->draft->paymentMethod !== NULL) {
                    $paymentMethod = $this->draft->paymentMethod;
                } else if($this->user !== NULL && $this->user->paymentMethod !== NULL) {
                    $paymentMethod = $this->user->paymentMethod;
                }
                if($paymentMethod !== NULL && !$paymentMethod->isEnabled) {
                    $paymentMethod = NULL;
                }
                $payDeposit = NULL;
                if($this->draft->payOnlyDeposit !== NULL) {
                    $payDeposit = $this->draft->payOnlyDeposit;
                } else if($this->user !== NULL && $this->user->payOnlyDeposit !== NULL) {
                    $payDeposit = $this->user->payOnlyDeposit;
                }

                $values = [];
                if($paymentMethod !== NULL) {
                    $values['paymentMethod'] = $paymentMethod->id;
                }
                if($payDeposit !== NULL) {
                    $values['payDeposit'] = intval($payDeposit);
                }
                $f->setValues($values);
            }
            $this->addComponent($f, 'finish');
        };
    }

    function handleUseCredit($credit = FALSE) {
        $this->draft->isPayingByCredit = $credit && $this->draft->user !== NULL && $this->draft->canPayByCredit;
        $this->logger->info(sprintf('Přihláška %s %sbude zaplacena kreditem.', $this->draft, ($this->draft->isPayingByCredit ? '' : 'ne')));
        $this->drafts->saveDraft($this->draft);
        $this->presenter->redirect('this');
    }

    function handleResetDiscountCode() {
        $this->logger->info(sprintf('Odebírám slevový kód z přihlášky %s', $this->draft));
        $this->draft->resetDiscountCode();
        $this->drafts->saveDraft($this->draft);
        $this->parent->flashMessage('Slevový kód byl odebrán z přihlášky.', 'success');
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');

        $this->template->draft = $this->draft;
        $this->template->countChildren = count($this->draft->children);
        $this->template->userEntity = $this->user;

        $this->template->render();
    }

    public function executeWithValidation($cb) {
        try {
            $returnValue = $cb();
            return $returnValue;
        } catch (DiscountCodeException $e) {
            $this->logger->notice(sprintf('Pokus o odeslání prihlášky %s selhal, protože vypršela platnost slevového kódu.', $this->draft));
            $this->draft->resetDiscountCode();
            $this->drafts->saveDraft($this->draft);
            $this->parent->flashMessage('Platnost Vašeho slevového kódu bohužel vypršela. Zkontrolujte si prosím novou cenu.', 'danger');
        } catch (DiscountException $e) {
            $this->logger->notice(sprintf('Pokus o odeslání přihlášky %s selhal, protože nastavená sleva už není platná.', $this->draft));
            $this->parent->flashMessage('Bohužel skončila slevová akce ještě než jste stihli přihlášku odeslat. Zkontrolujte si prosím novou cenu.', 'danger');
        } catch (FlashMessageException $e) {
            $this->parent->flashMessage($e->getFlashMessage());
        }
        $this->redirect('this');
    }

    private function buildPaymentMethods() {
        if($this->draft->isFullyPaidByCredit) {
            return NULL;
        }
        
        $methods = [];
        foreach($this->orm->paymentMethods->findEnabled() as $method) {
            $icon = $method->iconUrl;
            if(strpos($icon, '/') === 0) {
                $icon = $this->template->baseUri . $icon;
            }
            $methods[$method->id] = Html::el()->setHtml('<strong>' . $method->name . '</strong>' . (strlen($icon) > 0 ? '<br><img src="' . $icon . '" style="height:30px">' : ''));
        }
        return $methods;
    }

}
