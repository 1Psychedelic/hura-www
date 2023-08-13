<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\Facebook\FacebookAvatars;
use Hafo\Security\Storage\Avatars;
use Nette\Application\ForbiddenRequestException;
use Nette\Http\FileUpload;
use Nette\Utils\Image;
use VCD\UI\FrontModule\UserModule\AddPhotoControl;

class PhotoPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:User:Photo:default';

    function actionDefault() {
        $ap = $this->container->get(AddPhotoControl::class);
        $ap->onUpload[] = function(FileUpload $file) {
            $this->container->get(Avatars::class)->setAvatar($this->user->id, $file->toImage(), Avatars::SOURCE_UPLOAD);
            $this->redirect(ProfilePresenter::LINK_DEFAULT);
        };
        $ap->onDelete[] = function() {
            $this->container->get(Avatars::class)->deleteAvatar($this->user->id);
            $this->redirect(ProfilePresenter::LINK_DEFAULT);
        };
        $ap->onGoBack[] = function() {
            $this->redirect(ProfilePresenter::LINK_DEFAULT);
        };
        $this->addComponent($ap, 'photo');
        $this->template->fb = $this->userContext->getEntity()->facebookId;
    }

    function handleFacebookPhoto() {
        if($this->userContext->getEntity()->facebookId === NULL) {
            throw new ForbiddenRequestException;
        }
        $image = $this->container->get(FacebookAvatars::class)->download($this->userContext->getEntity()->facebookId, ['type' => 'large']);
        if($image instanceof Image) {
            $this->container->get(Avatars::class)->setAvatar($this->user->id, $image);
        }
        $this->redirect(ProfilePresenter::LINK_DEFAULT);
    }

}
