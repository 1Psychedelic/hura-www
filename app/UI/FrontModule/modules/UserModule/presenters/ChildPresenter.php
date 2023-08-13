<?php

namespace VCD\UI\FrontModule\UserModule;

use Nette\Application\ForbiddenRequestException;
use Nette\Database\Context;
use VCD\UI\FrontModule\EventsModule\EventTermControl;
use VCD2\Users\Service\UserContext;

class ChildPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:User:Child:default';

    function actionDefault($id) {

        $user = $this->container->get(UserContext::class)->getEntity();
        if(!$user) {
            throw new ForbiddenRequestException;
        }

        $child = $user->children->get()->getBy(['id' => $id]);
        if(!$child) {
            throw new ForbiddenRequestException;
        }

        $this->template->child = $child;

        foreach($child->eventsParticipated as $event) {
            if(!isset($this['term_' . $event->id])) {
                $this->addComponent(new EventTermControl($event->starts, $event->ends, FALSE), 'term_' . $event->id);
            }
        }

        // todo orm
        $db = $this->container->get(Context::class);
        $this->template->photos = function($event) use ($db) {
            return count($db->table('vcd_photo')->where('event = ? AND type = 0 AND visible = 1', $event)) > 0;
        };
        $this->template->ebook = function($event) use ($db) {
            return $db->table('vcd_ebook')->where('event = ? AND visible = 1', $event)->limit(1)->fetchField('id');
        };
    }

}
