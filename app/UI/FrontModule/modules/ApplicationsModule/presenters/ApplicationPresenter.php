<?php

namespace VCD\UI\FrontModule\ApplicationsModule;

use Hafo\UI\FlashMessage;
use Nette\Utils\Html;
use VCD\UI\FrontModule\EventsModule\EventPresenter;
use VCD\UI\FrontModule\UserModule\ApplicationPresenter as UserApplicationPresenter;
use VCD2\Applications\Child;
use VCD2\Applications\Service\Drafts;
use VCD2\Applications\Service\GoPay;
use VCD2\Applications\StepChoice;
use VCD2\Events\ApplicationStep;

class ApplicationPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Applications:Application:default';
    const LINK_PARENT_INFO = ':Front:Applications:Application:parent';
    const LINK_RETURN_FROM_GATEWAY = ':Front:Applications:Application:returnFromGateway';

    function actionDefault() {
        $this->requestValidParentInfo(FALSE);
        $this->requestValidChildren(FALSE);
        $this->requestValidSteps(FALSE);
        $this->redirect('finish');
    }

    function startup() {
        parent::startup();

        // akce skončila
        if($this->event->hasEnded) {
            $this->flashMessage('Tato akce již proběhla.', 'danger');
            $this->redirect(EventPresenter::LINK_DEFAULT, ['_event' => $this->event->slug]);
        }

        // přihlášky uzavřeny
        if(!$this->event->hasOpenApplications) {
            $this->flashMessage('Přihlášky na tuto akci jsou již uzavřeny.', 'danger');
            $this->redirect(EventPresenter::LINK_DEFAULT, ['_event' => $this->event->slug]);
        }

        $this->template->flashMessagesEnabled = FALSE;

        $countChildren = max(1, $this->draft->children->countStored());

        // moc dětí
        if(!$this->event->hasEnoughCapacityFor($countChildren) && $this->event->hasEnoughCapacityFor(1)) {
            $this['flashes']->addFlashMessage(new FlashMessage('danger', 'Je nám líto, z důvodu omezené kapacity nemůžeme přijmout tolik dětí.', FALSE));
        } else if(!$this->event->hasEnoughCapacityFor($countChildren)) {
            $this['flashes']->addFlashMessage(new FlashMessage('danger', 'Je nám líto, ale nejspíš vás někdo předběhl a už máme plno.', FALSE));
        }

        // náhradníci
        if($this->event->hasEnoughCapacityFor($countChildren) && $this->event->wouldBeReserves($countChildren)) {
            $this['flashes']->addFlashMessage(new FlashMessage('warning', sprintf('Je nám líto, ale máme plno. Stále však můžete přihlášku vyplnit a Vaše %s mezi náhradníky.', $countChildren > 1 ? 'děti budou zařazeny' : 'dítě bude zařazeno'), FALSE));
        }

        // uzavírka přihlášek
        if(!$this->event->hasOpenApplicationsAt((new \Nette\Utils\DateTime)->modify('+6 hours'))) {
            $this['flashes']->addFlashMessage(new FlashMessage('info', 'Pospěšte si prosím, přihlášky se za chvíli uzavřou!', FALSE, Html::el()->setHtml('<span class="glyphicon glyphicon-time pull-left"></span>')));
        }
    }

    function actionParent() {
        $control = new DraftParentControl($this->container, $this->draft);
        $control->onSave[] = function() {
            $this->redirect('default');
        };
        $this->addComponent($control, 'parent');

        if(!$this->user->isLoggedIn()) {
            $this['user']->setRedirectUrl($this->link('this'));
        }
    }

    function actionChildren() {
        $this->requestValidParentInfo();

        // redirect na formulář pokud nejsou žádné děti v profilu či přihlášce
        $user = $this->userContext->getEntity();
        if($this->draft->children->count() === 0 && ($user === NULL || $user->children->count() === 0)) {
            $this->redirect('child');
        }

        $control = new DraftChildrenControl($this->container, $this->draft);
        $this->addComponent($control, 'children');
    }

    function actionChild($id = NULL) {
        $this->requestValidParentInfo();
        if($id === NULL) {
            $this->requestFreeCapacity();
        }
        $control = new DraftChildControl($this->container, $this->draft, $id);
        $control->onSave[] = function() {
            $this->redirect('children');
        };
        $this->addComponent($control, 'child');
    }

    function actionChildDelete($id) {
        $this->requestValidParentInfo();

        $draftChild = $this->draft->children->get()->getBy(['id' => $id]);
        /** @var Child $draftChild */
        if($draftChild->child !== NULL && !$draftChild->child->isEditableByUser) {
            $this->orm->remove($draftChild);
            $this->orm->flush();
            $this->redirect('children');
        }
        
        $control = new DraftChildDeleteControl($this->container, $this->draft, $id);
        $control->onDelete[] = function() {
            $this->redirect('children');
        };
        $this->addComponent($control, 'childDelete');
    }

    function actionStep($id) {
        $this->requestValidParentInfo();
        $this->requestValidChildren();
        $control = new DraftStepControl($this->container, $this->draft, $id);
        $control->onSave[] = function() {
            $this->requestValidStepChoices();
            $this->redirect('default');
        };
        $this->addComponent($control, 'step');
        $this->template->id = $id;
    }

    function actionFinish() {
        $this->requestValidParentInfo();
        $this->requestValidChildren();
        $this->requestValidSteps();
        $control = new DraftFinishControl($this->container, $this->draft);
        $this->addComponent($control, 'finish');
    }
    
    function actionReturnFromGateway() {
        $payment = $this->draft->createdGoPayPayment;
        if($payment === NULL) {
            $this->flashMessage('Přihláška nemá přiřazenou platbu - přihlášku není možné odeslat.', 'danger');
            $this->redirect('finish');
        }

        $this->container->get(GoPay::class)->refreshStatus($this->draft);

        if($payment->hasFailed) {
            $this->flashMessage('Platba byla stornována - přihlášku není možné odeslat.', 'danger');
            $this->redirect('finish');
        }
        
        $control = new DraftFinishControl($this->container, $this->draft);
        $this->addComponent($control, 'finish');
        
        $control->executeWithValidation(function () {
            $this->container->get(Drafts::class)->finishDraft($this->draft);
            $this->presenter->redirect(UserApplicationPresenter::LINK_DEFAULT, ['id' => $this->draft->id, 'hash' => $this->draft->hash]);
        });
    }

    private function requestValidParentInfo($showWarning = TRUE) {
        if(!$this->draft->hasValidParentInfo) {
            $this->conditionalFlashMessage($showWarning, 'Před pokračováním musíte vyplnit tento formulář.', 'danger');
            $this->redirect('parent');
        }
    }

    private function requestValidChildren($showWarning = TRUE) {
        if(!$this->draft->hasValidChildren) {
            $this->conditionalFlashMessage($showWarning, 'Před pokračováním musíte přidat aspoň jedno dítě.', 'danger');
            $this->redirect('children');
        }
        if(!$this->event->hasEnoughCapacityFor($this->draft->children->countStored()) && $this->event->hasEnoughCapacityFor(1)) {
            $this->redirect('children');
        }
    }

    private function requestValidSteps($showWarning = TRUE) {
        // nevyplněné mezikroky
        if(count($this->draft->unfilledSteps) > 0) {
            /** @var ApplicationStep $step */
            $step = reset($this->draft->unfilledSteps);
            $this->conditionalFlashMessage($showWarning, 'Před pokračováním musíte vyplnit tento formulář.', 'danger');
            $this->redirect('step', ['id' => $step->slug]);
        }

        $this->requestValidStepChoices($showWarning);
    }

    private function requestValidStepChoices($showWarning = TRUE) {
        // vyplněné ale už nedostupné
        if(count($this->draft->invalidStepChoices) > 0) {
            /** @var StepChoice $stepChoice */
            $stepChoice = reset($this->draft->invalidStepChoices);
            $this->conditionalFlashMessage($showWarning, 'Bohužel vás někdo předběhl a volba "' . $stepChoice->option->option . '" již není dostupná. Vyberte prosím něco jiného.');
            $this->redirect('step', ['id' => $stepChoice->step->slug]);
        }
    }

    private function requestFreeCapacity($showWarning = TRUE) {
        if(!$this->event->hasEnoughCapacityFor($this->draft->children->countStored() + 1)) {
            $this->conditionalFlashMessage($showWarning, 'Bohužel pro víc dětí už nemáme kapacitu.', 'danger');
            $this->redirect('children');
        }
    }

    private function conditionalFlashMessage($show, $message, $type = 'danger') {
        if($show) {
            $this->flashMessage($message, $type);
        }
    }

}
