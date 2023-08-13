<?php

namespace VCD2\Events;

use DateTimeImmutable;
use Hafo\Orm\Entity\Entity;
use Nette\Utils\Strings;
use Nextras\Orm\Collection\ICollection;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property Event $event {m:1 Event::$tabs}
 * @property string $slug
 * @property string $name
 * @property int $position
 * @property string|NULL $content
 *
 * 
 */
class EventTab extends Entity {

    function __construct(Event $event, $name, $content = NULL) {
        parent::__construct();

        $this->event = $event;
        $this->name = $name;
        $this->content = $content;

        $this->generateSlug();
        $this->resolvePosition();
    }

    public function moveLeft() {
        $this->swapPositionWith($this->getPreviousTab());
    }

    public function moveRight() {
        $this->swapPositionWith($this->getNextTab());
    }

    private function swapPositionWith(EventTab $other = NULL) {
        if($other === NULL) {
            return;
        }
        $pos = $other->position;
        $other->position = $this->position;
        $this->position = $pos;
    }

    /** @return self|NULL */
    private function getPreviousTab() {
        return $this->event->tabs->get()->orderBy('position', ICollection::DESC)->getBy(['position<' => $this->position]);
    }

    /** @return self|NULL */
    private function getNextTab() {
        return $this->event->tabs->get()->orderBy('position', ICollection::ASC)->getBy(['position>' => $this->position]);
    }

    private function generateSlug() {
        $error = 0;
        $slug = NULL;
        do {
            $slug = Strings::webalize($this->name) . ($error !== 0 ? '-' . $error : '');
            foreach($this->event->tabs->get()->findBy(['slug' => $slug]) as $tab) {
                if($tab !== $this) {
                    $error++;
                    $slug = NULL;
                    break;
                }
            }
        } while ($slug === NULL);

        $this->slug = $slug;
    }

    private function resolvePosition() {
        /** @var self|NULL $last */
        $last = $this->event->tabs->get()->orderBy('position', ICollection::DESC)->fetch();
        if($last === NULL) {
            $this->position = 0;
        } else {
            $this->position = $last->position + 1;
        }
    }

    public function onBeforePersist()
    {
        $this->event->updatedAt = new DateTimeImmutable();
    }
}
