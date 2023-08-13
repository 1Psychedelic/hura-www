<?php

namespace VCD2\Users\Service;

use Nette\Forms\Form;
use Nette\Http\IRequest;
use Nette\SmartObject;
use VCD2\Orm;
use VCD2\Users\Consent;

/**
 * @method onConsentAdded(Consent $consent)
 */
class Consents {

    use SmartObject;

    public $onConsentAdded = [];

    private $orm;

    private $userContext;

    private $request;

    function __construct(Orm $orm, UserContext $userContext, IRequest $request) {
        $this->orm = $orm;
        $this->userContext = $userContext;
        $this->request = $request;
    }

    function addConsent($type, $consentText, $email = NULL) {
        $ip = $this->request->getRemoteAddress();
        $consent = new Consent($type, $consentText, $ip, $this->getUser(), $email);
        $this->orm->persistAndFlush($consent);

        $this->onConsentAdded($consent);
    }

    function addConsentCheckbox(Form $form, $type, $consentText, $id = NULL, $required = TRUE, $submitField = NULL, $getEmailFunction = NULL) {
        $checkbox = $form->addCheckbox($id === NULL ? 'consent_' . $type : $id, $consentText);
        if($required) {
            $checkbox->setRequired($required);
        }
        $form->onSuccess[] = function(Form $form) use ($type, $consentText, $getEmailFunction, $submitField) {
            if($submitField === NULL || $form->isSubmitted() === $form[$submitField]) {
                $data = $form->getValues(TRUE);
                $email = is_callable($getEmailFunction) ? $getEmailFunction($data, $form) : NULL;

                $this->addConsent($type, $consentText, $email);
            }
        };
    }

    function cancelConsent($type) {
        $user = $this->getUser();
        $consents = $this->orm->consents->findBy([
            'user' => $user->id,
            'type' => $type,
        ]);
        foreach($consents as $consent) {
            $this->orm->remove($consent);
        }
        $this->orm->flush();
    }
    
    function hasValidConsent($type) {
        $user = $this->getUser();
        if($user === NULL) {
            return FALSE;
        }
        $consents = $this->orm->consents->findBy([
            'user' => $user->id,
            'type' => $type,
        ]);
        $now = new \DateTimeImmutable;
        foreach($consents as $consent) {
            if($consent->expiresAt > $now) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function getUser() {
        return $this->userContext->getEntity();
    }

}
