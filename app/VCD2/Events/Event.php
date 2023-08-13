<?php

namespace VCD2\Events;

use Hafo\Orm\Entity\Entity;
use Hafo\Persona\Gender;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Repository\IRepository;
use VCD2\Applications\Application;
use VCD2\Applications\Child;
use VCD2\Discounts\Discount;
use VCD2\Ebooks\Ebook;
use VCD2\Emails\Email;
use VCD2\Gallery\AbstractPhoto;
use VCD2\Users\User;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property string $slug
 * @property int $type {enum self::TYPE_*}
 * @property bool $visible {default FALSE}
 * @property bool $isArchived {default FALSE}
 * @property string $name
 * @property string|NULL $subheading
 * @property string|NULL $description
 * @property string|NULL $keywords
 * @property string|NULL $bannerSmall
 * @property string|NULL $bannerLarge
 * @property string|NULL $galleryPhoto
 * @property Email|NULL $acceptedEmail {m:1 Email, oneSided=TRUE}
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable $createdAt {default now}
 * @property \DateTimeImmutable $updatedAt {default now}
 * @property \DateTimeImmutable $starts
 * @property \DateTimeImmutable $ends
 * @property \DateTimeImmutable|NULL $applicableUntil
 *
 *
 **** Kapacita
 * @property-read Child[] $participants {virtual}
 * @property int $maxParticipants
 * @property int $changeParticipants {default 0}
 * @property-read Child[] $reserves {virtual}
 * @property int $maxReserves
 * @property-read int $capacity {virtual}
 *
 *
 **** Věkové omezení
 * @property int $ageMin
 * @property int $ageMax
 * @property int $ageCap
 *
 *
 **** Cena
 * @property int $price
 * @property int $deposit {default 0}
 * @property int $siblingDiscount {default 0}
 * @property int|NULL $priceVip
 *
 *
 **** Slevy
 * @property Discount[]|OneHasMany $discounts {1:m Discount::$event}
 * @property-read Discount|NULL $currentDiscount {virtual}
 * @property-read Discount $currentPriceLevel {virtual}
 * @property-read Discount $currentPriceOrDiscount {virtual}
 * @property int $priceBeforeDiscount {virtual}
 * @property-read \DateTimeImmutable|NULL $discountedUntil {virtual}
 *
 *
 **** Příznaky
 * @property-read bool $hasStarted {virtual}
 * @property-read bool $hasEnded {virtual}
 * @property-read bool $isNow {virtual}
 * @property-read bool $hasOpenApplications {virtual}
 * @property-read bool $isDiscounted {virtual}
 * @property-read bool $hasPhotos {virtual}
 *
 *
 **** Mezikroky
 * @property OneHasMany|ApplicationStep[] $steps {1:m ApplicationStep::$event, orderBy=[position=ASC]}
 * @property OneHasMany|EventAddon[] $addons {1:m EventAddon::$event, orderBy=[position=ASC]}
 *
 *
 **** Záložky/fotky/ebooky
 * @property EventTab[]|OneHasMany $tabs {1:m EventTab::$event, orderBy=[position=ASC]}
 * @property Ebook[]|OneHasMany $ebooks {1:m Ebook::$event}
 * @property AbstractPhoto[]|OneHasMany $allPhotos {1:m AbstractPhoto::$event}
 * @property EventImage[]|OneHasMany $images {1:m EventImage::$event, orderBy=[position=ASC]}
 * @property string|null $sidebarHtml
 *
 *
 **** Omezení
 * @property ManyHasMany|User[] $openForUsers {m:m User::$eventsForUser, isMain=TRUE}
 * @property bool $openForVip {default FALSE}
 *
 *
 **** Přihlášky
 * @property Application[]|OneHasMany $applications {1:m Application::$event}
 * @property Application[] $newApplications {virtual}
 * @property Application[] $acceptedApplications {virtual}
 * @property User[] $acceptedUsers {virtual}
 *
 *
 **** Děti
 * @property Child[] $appliedChildren {virtual}
 * @property Child[] $acceptedChildren {virtual}
 * @property int[] $acceptedByGender {virtual}
 *
 **** Schema.org
 * @property string|null $schemaLocationName {default NULL}
 * @property string|null $schemaLocationAddressPostalCode {default NULL}
 * @property string|null $schemaLocationAddressRegion {default NULL}
 * @property string|null $schemaLocationAddressLocality {default NULL}
 */
class Event extends Entity
{
    const TYPE_CAMP = 1;
    const TYPE_TRIP = 2;
    const TYPE_CAMP_SPRING = 3;

    const TYPES_AVAILABLE = [
        self::TYPE_CAMP,
        self::TYPE_TRIP,
        self::TYPE_CAMP_SPRING,
    ];

    const TYPES_IDS = [
        self::TYPE_CAMP => 'camp',
        self::TYPE_TRIP => 'trip',
        self::TYPE_CAMP_SPRING => 'camp',
    ];

    const TYPES_NAMES = [
        self::TYPE_TRIP => 'Výlet',
        self::TYPE_CAMP => 'Tábor',
        //self::TYPE_CAMP => 'Letní tábor',
        //self::TYPE_CAMP_SPRING => 'Jarní tábor',
    ];

    const SLUG_TO_TYPE_MAP = [
        'tabor' => [Event::TYPE_CAMP, Event::TYPE_CAMP_SPRING],
        'vylet' => [Event::TYPE_TRIP],
    ];

    public function __construct($type, $name, \DateTimeInterface $starts, \DateTimeInterface $ends, $maxParticipants, $maxReserves, $ageMin, $ageMax, $price, $deposit, $siblingDiscount, $ageCap = null)
    {
        parent::__construct();

        // validate name
        if (!strlen($name)) {
            throw EventException::create('Name cannot be empty.', 'Nebyl zadán název akce.');
        }

        // validate type
        if (!in_array($type, self::TYPES_AVAILABLE, true)) {
            throw EventException::create('Unknown type.', 'Neznámý druh akce.');
        }

        // validate time
        if ($starts >= $ends) {
            throw EventException::create('Event cannot start after it ends.', 'Začátek akce musí být dřív než konec akce.');
        }

        // validate&fix age
        if ($ageMax < $ageMin) {
            $tmp = $ageMax;
            $ageMax = $ageMin;
            $ageMin = $tmp;
        }

        $this->type = $type;
        $this->name = $name;
        $this->starts = $starts;
        $this->ends = $ends;

        $this->maxParticipants = max(0, $maxParticipants);
        $this->maxReserves = max(0, $maxReserves);

        $this->ageMin = max(0, $ageMin);
        $this->ageMax = max(0, $ageMax);
        $this->ageCap = max(0, $ageMax, $ageCap);

        $this->price = max(0, $price);
        $this->deposit = max(0, $deposit);
        $this->siblingDiscount = max(0, $siblingDiscount);

        $this->generateSlug();
    }

    public function onBeforeUpdate()
    {
        parent::onBeforeUpdate();
        $this->updatedAt = new \DateTime;
    }

    public function createApplication(User $user = null)
    {
        return new Application($this, $user);
    }

    public function countFreeSlots($includingReserves = false)
    {
        $maxCapacity = $this->maxParticipants + ($includingReserves ? $this->maxReserves : 0);
        $capacity = $this->changeParticipants + count($this->participants) + ($includingReserves ? count($this->reserves) : 0);

        return max(0, $maxCapacity - $capacity);
    }

    public function hasEnoughCapacityFor($countChildren)
    {
        return $this->changeParticipants + count($this->participants) + count($this->reserves) + $countChildren <= $this->maxParticipants + $this->maxReserves;
    }

    public function wouldBeReserves($countChildren)
    {
        return $this->changeParticipants + count($this->participants) + $countChildren > $this->maxParticipants;
    }

    public function siblingDiscountValueFor($countChildren)
    {
        if ($countChildren > 1) {
            return $countChildren * $this->siblingDiscount;
        }

        return 0;
    }

    public function generateSlug($version = null)
    {
        $this->slug = Strings::webalize($this->name) . ($version > 0 ? '-' . $version : '');
    }

    public function areApplicationsOpenForUser(User $user = null)
    {

        // Admin může vše
        if ($user !== null && $user->isAdmin()) {
            return true;
        }

        // Nezveřejněná akce
        if (!$this->visible) {
            return false;
        }

        // Konkrétní lidé
        if ($this->openForUsers->count() > 0) {
            if ($user === null) {
                return false;
            }
            if ($this->openForUsers->has($user)) {
                return true;
            }
        }

        // VIP
        if ($this->openForVip) {
            return $user !== null && $user->isVip;
        } elseif ($this->openForUsers->count() > 0) {
            return false; // Pouze konkrétní lidi a žádné VIP
        }

        // Bez omezení
        return true;
    }

    /**
     * @param string|NULL $slug
     * @return EventTab|NULL
     */
    public function getTab($slug = null)
    {
        return $slug === null ? $this->tabs->get()->fetch() : $this->tabs->get()->getBy(['slug' => $slug]);
    }

    public function getCurrentPrice(int $vipLevel = 0): int
    {
        if ($vipLevel === 2) {
            return 0;
        }

        $currentDiscount = $this->currentDiscount;

        if ($currentDiscount !== null) {
            if ($vipLevel === 1) {
                return $currentDiscount->priceVip;
            }

            return $currentDiscount->price;
        }

        return $this->getCurrentPriceBeforeDiscount($vipLevel);
    }

    public function getCurrentPriceBeforeDiscount(int $vipLevel = 0): int
    {
        if ($vipLevel === 2) {
            return 0;
        }

        $currentPriceLevel = $this->currentPriceLevel;

        if ($vipLevel === 1) {
            return $currentPriceLevel->priceVip;
        }

        return $currentPriceLevel->price;
    }

    protected function getterHasStarted()
    {
        return $this->starts <= new \DateTime;
    }

    protected function getterHasEnded()
    {
        return $this->ends <= new \DateTime;
    }

    protected function getterIsNow()
    {
        return $this->hasStarted && !$this->hasEnded;
    }

    protected function getterAcceptedApplications()
    {
        $result = [];
        foreach ($this->applications as $application) {
            if ($application->isAccepted) {
                $result[] = $application;
            }
        }

        return $result;
    }

    protected function getterNewApplications()
    {
        $result = [];
        foreach ($this->applications as $application) {
            if ($application->isNew) {
                $result[] = $application;
            }
        }

        return $result;
    }

    protected function getterAcceptedChildren()
    {
        $result = [];
        foreach ($this->acceptedApplications as $application) {
            foreach ($application->children as $child) {
                $result[] = $child;
            }
        }

        return $result;
    }

    protected function getterAppliedChildren()
    {
        $result = [];
        $applications = $this->applications->get()->findBy(['appliedAt!=' => null, 'canceledAt' => null, 'rejectedAt' => null]);
        foreach ($applications as $application) {
            foreach ($application->children as $child) {
                $result[] = $child;
            }
        }

        return $result;
    }

    protected function getterHasOpenApplications()
    {
        return $this->hasOpenApplicationsAt(new \DateTimeImmutable);
    }

    public function hasOpenApplicationsAt(\DateTimeInterface $at)
    {
        return $this->applicableUntil > $at;
    }

    protected function getterCapacity()
    {
        return intval(min(count($this->participants) + $this->changeParticipants, $this->maxParticipants) / $this->maxParticipants * 100);
    }

    protected function getterAcceptedByGender()
    {
        $data = [
            Gender::MALE => 0,
            Gender::FEMALE => 0,
        ];
        foreach ($this->acceptedChildren as $child) {
            $data[$child->gender]++;
        }

        return $data;
    }

    public function getPriceInfo(User $user = null): EventPriceInfo
    {
        if ($user !== null && $user->isVip) {
            if ($this->currentDiscount !== null && $this->currentDiscount->priceVip > 0) {
                // VIP cena ve slevě
                $basePrice = $this->currentPriceLevel->priceVip > 0 ? $this->currentPriceLevel->priceVip : $this->currentPriceLevel->price;

                return new EventPriceInfo(
                    $basePrice,
                    $this->currentDiscount->priceVip,
                    $this->currentDiscount->ends
                );
            } elseif ($this->currentDiscount !== null) {
                // Sleva bez VIP ceny
                if ($this->currentPriceLevel->priceVip > 0 && $this->currentPriceLevel->priceVip < $this->currentDiscount->price) {
                    return new EventPriceInfo(
                        $this->currentPriceLevel->priceVip,
                        $this->currentDiscount->price,
                        $this->currentDiscount->ends
                    );
                }
            }

            return new EventPriceInfo(
                $this->currentPriceLevel->price,
                $this->currentPriceLevel->priceVip,
                null
            );
        }

        return new EventPriceInfo(
            $this->currentPriceLevel->price,
            $this->currentDiscount !== null ? $this->currentDiscount->price : null,
            $this->currentDiscount !== null ? $this->currentDiscount->ends : null
        );
    }

    protected function getterIsDiscounted()
    {
        return $this->currentDiscount !== null;
    }

    protected function getterPrice($price)
    {

        // hack pro ukládání
        if ($this->isModified('price')) {
            return $price;
        }

        if ($this->isDiscounted) {
            return $this->currentDiscount->price;
        }

        return $price;
    }

    protected function getterPriceBeforeDiscount()
    {
        return $this->getRawValue('price');
    }

    protected function setterPriceBeforeDiscount($price)
    {
        $this->price = $price;

        return $price;
    }

    protected function getterDiscountedUntil()
    {
        if ($this->isDiscounted) {
            return $this->currentDiscount->ends;
        }

        return null;
    }

    protected function getterParticipants()
    {
        $participants = [];
        foreach ($this->acceptedChildren as $child) {
            if (!$child->application->isReserve) {
                $participants[] = $child;
            }
        }

        return $participants;
    }

    protected function getterReserves()
    {
        $reserves = [];
        foreach ($this->acceptedChildren as $child) {
            if ($child->application->isReserve) {
                $reserves[] = $child;
            }
        }

        return $reserves;
    }

    protected function getterCurrentDiscount()
    {
        $now = new \DateTimeImmutable();

        return $this->discounts->get()->getBy(['starts<' => $now, 'ends>' => $now, 'isDiscount' => true]);
    }

    protected function getterCurrentPriceLevel()
    {
        $now = new \DateTimeImmutable();
        $priceLevel = $this->discounts->get()->getBy(['starts<' => $now, 'ends>' => $now, 'isDiscount' => false]);

        if ($priceLevel === null) {
            $priceLevel = new Discount(
                $this,
                new \DateTimeImmutable('now'),
                $this->starts,
                $this->priceBeforeDiscount,
                $this->priceVip === null ? $this->priceBeforeDiscount : $this->priceVip,
                false
            );
        }

        return $priceLevel;
    }

    protected function getterCurrentPriceOrDiscount()
    {
        $discount = $this->currentDiscount;
        if ($discount !== null) {
            return $discount;
        }

        return $this->currentPriceLevel;
    }

    protected function getterHasPhotos()
    {
        return $this->allPhotos->get()->findBy(['type' => AbstractPhoto::TYPE_PHOTO, 'visible' => true])->countStored() > 0;
    }

    protected function getterAcceptedUsers()
    {
        $users = [];
        foreach ($this->acceptedApplications as $application) {
            if ($application->user === null) {
                continue;
            }
            $users[$application->user->id] = $application->user;
        }

        return $users;
    }

    public function __toString()
    {
        return sprintf('#%s(%s)', $this->id, $this->slug);
    }

    public static function createFromArray(array $data, IRepository $repository = null)
    {
        $event = new self(
            $data['type'],
            $data['name'],
            DateTime::from($data['starts']),
            DateTime::from($data['ends']),
            $data['maxParticipants'],
            $data['maxReserves'],
            $data['ageMin'],
            $data['ageMax'],
            $data['priceBeforeDiscount'],
            $data['deposit'],
            $data['siblingDiscount'],
            $data['ageCap'] ?? null
        );
        if ($repository !== null) {
            $repository->attach($event);
        }
        $event->setValues($data);

        return $event;
    }
}
