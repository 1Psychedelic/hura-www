<?php

namespace VCD2\Ebooks;

use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use VCD2\Entity;
use VCD2\Events\Event;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property ManyHasOne|Event|NULL $event {m:1 Event::$ebooks}
 * @property string $name
 * @property string $description
 * @property string $image
 * @property string $ebook
 * @property int $position
 * @property bool $visible
 *
 *
 **** Stažení
 * @property OneHasMany|EbookDownload[] $downloads {1:m EbookDownload::$ebook}
 * @property int $countDownloads {virtual}
 * @property int $downloaded
 */
class Ebook extends Entity {

    function __construct($name, $description, $image, $ebook, $position = 0, $visible = FALSE, $downloaded = 0, Event $event = NULL) {
        parent::__construct();
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->ebook = $ebook;
        $this->position = $position;
        $this->visible = $visible;
        $this->downloaded = $downloaded;
        $this->event = $event;
    }
    
    protected function getterCountDownloads() {
        return $this->downloads->countStored() + $this->downloaded;
    }

}
