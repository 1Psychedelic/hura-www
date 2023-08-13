<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\IResponse;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD\UI\FrontModule\ApplicationsModule\FeedbackControlFactory;
use VCD\UI\FrontModule\EventsModule\EventTermControl;
use VCD2\Applications\Application;
use VCD2\Applications\PaymentMethod;
use VCD2\Applications\Service\InvoiceGenerator;
use VCD2\Emails\Service\Emails\AccountCreatedMail;
use VCD2\Emails\Service\Emails\ApplicationAppliedMail;
use VCD2\Emails\Service\Emails\EmailVerifyMail;
use VCD2\Orm;
use VCD2\Users\Service\UserContext;

class ApplicationControl extends Control {

    private $container;
    
    private $application;

    function __construct(Container $container, Application $application) {
        $this->container = $container;
        $this->application = $application;

        $this->onAnchor[] = function() use ($container, $application) {
            $this->template->application = $application;
            $this->addComponent(new EventTermControl($application->event->starts, $application->event->ends, FALSE), 'term');
            $this->template->paymentMethodBankTransfer = PaymentMethod::ID_BANK_TRANSFER;
            $this->template->bankAccount = $container->get(Context::class)->table('system_website')->fetch()['bank_account'];

            $this->addComponent($container->get(FeedbackControlFactory::class)->create($application->id), 'feedback');

            $user = $container->get(UserContext::class)->getEntity();
            if($user !== NULL && !$user->emailVerified) {
                /** @var Form $f */
                $f = $container->get(FormFactory::class)->create();
                $f->setRenderer(new BootstrapRenderer);
                if(!$user->emailVerified) {
                    $f->addText('email', 'E-mail')->setRequired()->setValue($user->email)->addRule(Form::EMAIL);
                }
                if(!$user->phoneVerified) {
                    $f->addText('phone', 'Telefon')->setRequired()->setValue($user->phone);
                }
                $f->addSubmit('save', 'Opravit údaje a znovu poslat e-mail');
                $f->onSuccess[] = function(Form $f) use ($container, $user) {
                    if($f->isSubmitted() === $f['save']) {

                        $orm = $container->get(Orm::class);

                        $data = $f->getValues(TRUE);

                        if(!$user->emailVerified) {
                            $this->application->email = $data['email'];
                        }
                        if(!$user->phoneVerified) {
                            $this->application->phone = $data['phone'];
                        }

                        $orm->persist($this->application);

                        if($user !== NULL) {
                            if(!$user->emailVerified) {
                                $user->email = $data['email'];
                            }
                            if(!$user->phoneVerified) {
                                $user->phone = $data['phone'];
                            }
                            $orm->persist($user);
                        }

                        $orm->flush();

                        if($user->canLogin) {
                            $container->get(EmailVerifyMail::class)->send($user->email, $user->emailVerifyHash);
                        } else {
                            $container->get(AccountCreatedMail::class)->send($user->email);
                        }
                        $container->get(ApplicationAppliedMail::class)->send($this->application->id);

                        $this->presenter->redirect('this');
                    }
                };
                $this->addComponent($f, 'fixInfoForm');
            }
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }
    
    function handleInvoice() {
        if($this->application->invoice === NULL) {
            throw new BadRequestException;
        }
        $raw = $this->container->get(InvoiceGenerator::class)->generate($this->application->invoice);
        $this->container->get(IResponse::class)->setContentType('application/pdf');
        $this->presenter->sendResponse(new TextResponse($raw));
    }

}
