<?php

use Hafo\DI\Container;
use Nette\Security\Identity;
use Nette\Security\User;
use Hafo\Facebook\FacebookAvatars;
use Hafo\Security\Storage\Avatars;
use Nette\Utils\Image;

return [

    User::class => function(User $user, Container $c) {
        $user->setExpiration('+30 days', FALSE, TRUE);

        // download profile picture
        $user->onLoggedIn[] = function(Nette\Security\User $user) use ($c) {
            $identity = $user->getIdentity();
            if($identity instanceof Identity && isset($identity->data['facebook_id'])) {
                $fbid = $identity->data['facebook_id'];
                $id = $user->getId();

                $fa = $c->get(FacebookAvatars::class);
                $a = $c->get(Avatars::class);
                $isFromFacebook = $a->isFromSource($id, [Avatars::SOURCE_FACEBOOK, Avatars::SOURCE_UNKNOWN]);
                if($isFromFacebook && $a->isOlderThan($id, '-1 month')) {
                    try {
                        $image = $fa->download($fbid, ['type' => 'large']);
                        if($image instanceof Image) {
                            $a->setAvatar($id, $image);
                        }
                    } catch (\Exception $e) {
                        \Tracy\Debugger::log($e, \Tracy\ILogger::ERROR);
                    }
                }
            }
        };
    }

];
