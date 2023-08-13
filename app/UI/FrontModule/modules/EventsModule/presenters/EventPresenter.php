<?php

namespace VCD\UI\FrontModule\EventsModule;

use Nette\Application\BadRequestException;
use Nette\Database\Context;
use Nette\Http\Request;
use VCD\UI\FrontModule\GalleryModule\PhotosPresenter;
use VCD\UI\FrontModule\WebModule\EbooksPresenter;
use VCD2\Events\Event;

class EventPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Events:Event:default';

    /** @persistent */
    public $_event;

    /** @var Event|NULL */
    protected $event;

    function actionDefault($id, $tab = NULL, $ref = NULL) {
        $db = $this->container->get(Context::class);

        if($id !== NULL && empty($this->_event)) {
            $this->logger->withName('vcd.debug')->addDebug(sprintf('Špatný link na akci %s z %s', $id, (string)$this->container->get(Request::class)->getReferer()));
            $this->presenter->redirect(self::LINK_DEFAULT, ['_event' => $id, 'tab' => $tab, 'ref' => $ref]);
        }

        $cond = ['slug' => $this->_event];
        if(!$this->user->isInRole('admin')) {
            $cond['visible'] = TRUE;
        }
        $this->event = $this->orm->events->getBy($cond);
        
        if($this->event === NULL) {
            throw new BadRequestException;
        }
        
        $this->template->event = $this->event;

        if($this->event->keywords) {
            $this->template->keywords = $this->event->keywords;
        }

        $this->template->description = $this->event->description;
        $this->template->titlePrefix = $this->event->name;

        $this->template->currentTab = $this->event->getTab($tab);

        // todo orm
        $this->template->photos = count($db->table('vcd_photo')->where('event = ? AND type = 0 AND visible = 1', $this->event->id)) > 0;
        $this->template->ebook = $db->table('vcd_ebook')->where('event = ? AND visible = 1', $this->event->id)->limit(1)->fetchField('id');
        $this->template->ebooksLink = EbooksPresenter::LINK_DEFAULT;
        $this->template->photosLink = PhotosPresenter::LINK_DEFAULT;

        if($this->isAjax() && $ref === 'tab') {
            $this->redrawControl('tabs');
        }

        $this->addComponent(new EventDetailBox($this->event, $this->userContext->getEntity()), 'box');
    }

    protected function getOgMetaDescription() {
        return $this->event->description;
    }

}
