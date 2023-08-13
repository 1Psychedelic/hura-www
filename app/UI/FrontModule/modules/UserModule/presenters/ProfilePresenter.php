<?php

namespace VCD\UI\FrontModule\UserModule;

use Nette\Application\Responses\CallbackResponse;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Utils\Json;

class ProfilePresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:User:Profile:default';
    const LINK_PARENT = ':Front:User:Profile:parent';
    const LINK_CHILD = ':Front:User:Profile:child';

    function actionDefault($canceled = FALSE) {
        $this->template->titlePrefix = 'Profil';
        $this->addComponent(new ProfileControl($this->container, $canceled), 'profile');
    }

    function actionParent() {
        $this->addComponent(new ProfileParentControl($this->container), 'profile');
    }

    function actionChild($id = NULL) {
        $this->template->id = $id;
        $this->template->childProfileLink = ChildPresenter::LINK_DEFAULT;
        $this->template->profileLink = self::LINK_DEFAULT;
        $this->addComponent(new ProfileChildControl($this->container, $id), 'profile');
    }

    // todo přihlášky?
    function actionJson() {
        $user = $this->userContext->getEntity();

        $children = [];
        foreach($user->children as $child) {
            $children[] = (object)[
                'Jméno a příjmení' => $child->name,
                'Datum narození' => $child->dateBorn->format('d. m. Y'),
                'Pohlaví' => $child->gender === 'm' ? 'Chlapec' : 'Dívka',
                'Plavec' => $child->swimmer,
                'ADHD nebo podobná diagnóza' => $child->adhd,
                'Zdravotní stav' => $child->health,
                //'Alergie' => $child->allergy,
                'Poznámka' => $child->notes,
            ];
        }

        $parent = (object) [
            'Jméno a příjmení' => $user->name,
            'E-mail' => $user->email,
            'Telefon' => $user->phone,
            'Ulice a číslo domu' => $user->street,
            'Město' => $user->city,
            'PSČ' => $user->zip,
            'IP' => $user->ip,
            'Host' => $user->host,
            'Facebook ID' => $user->facebookId,
            'Facebook jméno' => $user->facebookName,
            'Facebook křestní jméno' => $user->facebookFirstName,
            'Facebook prostřední jméno' => $user->facebookMiddleName,
            'Facebook příjmení' => $user->facebookLastName,
            'Facebook pohlaví' => $user->facebookGender,
            'Facebook link' => $user->facebookLink,
            'Facebook e-mail' => $user->facebookEmail,
            'Facebook web' => $user->facebookWebsite,
            'Google ID' => $user->googleId,
            'Google jméno' => $user->googleName,
            'Google e-mail' => $user->googleEmail,
            'Google link' => $user->googleLink,
        ];

        $data = (object)[
            'Zákonný zástupce' => $parent,
            'Seznam dětí v profilu' => $children
        ];

        $this->sendResponse(new CallbackResponse(function(IRequest $httpRequest, IResponse $httpResponse) use ($data) {
            $httpResponse->setContentType('application/json', 'utf-8');
            echo Json::encode($data, Json::PRETTY);
        }));
    }

}
