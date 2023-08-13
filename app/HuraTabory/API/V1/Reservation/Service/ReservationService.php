<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Reservation\Service;

use DateTimeImmutable;
use VCD2\Applications\Application;
use VCD2\Applications\ApplicationAddon;
use VCD2\Applications\Child as ApplicationChild;
use VCD2\Orm;
use VCD2\Users\Child;

class ReservationService
{
    /** @var Orm */
    private $orm;

    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }

    public function upsertParentInfo(Application $draft, array $data, bool $flush = false): void
    {
        $draft->updateParentInfo(
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['city'],
            $data['street'],
            $data['zip']
        );
        $draft->markAgreement($data['agreeGdpr'], $data['agreeTermsAndConditions']);

        $draft->user->updateInfo(
            $data['name'],
            $data['phone'],
            $data['city'],
            $data['street'],
            $data['zip']
        );
        $draft->user->markAgreement($data['agreeGdpr'], $data['agreeTermsAndConditions']);

        $this->orm->persist($draft->user);

        if ($flush) {
            $draft->refreshDiscount();
            $draft->recalculatePrice();
            $this->orm->persist($draft);
            $this->orm->flush();
        }
    }

    public function upsertChildren(Application $draft, array $childrenData, bool $flush = false): void
    {
        foreach ($childrenData as $childData) {
            $child = null;
            if (is_int($childData['childId'])) {
                $child = $this->orm->children->get($childData['childId']);
                if (!$child->parents->has($draft->user)) {
                    $child = null;
                }
            }

            if ($child === null) {
                $child = Child::createFromArray($draft->user, $childData);
                $this->orm->persistAndFlush($child);
            } else {
                $child->updateInfo(
                    $childData['name'],
                    $childData['gender'],
                    new DateTimeImmutable($childData['dateBorn']),
                    $childData['swimmer'],
                    $childData['adhd'],
                    $childData['health'],
                    null,
                    null
                );
                $this->orm->persist($child);
            }

            $applicationChild = $this->orm->applicationChildren->getBy([
                'application' => $draft->id,
                'child' => $child->id,
            ]);

            if ($childData['isInReservation']) {
                if ($applicationChild === null) {
                    $applicationChild = ApplicationChild::createFromArray($draft, $child, $childData);
                } else {
                    $applicationChild->updateInfo(
                        $childData['name'],
                        $childData['gender'],
                        new DateTimeImmutable($childData['dateBorn']),
                        $childData['swimmer'],
                        $childData['adhd'],
                        $childData['health'],
                        null,
                        null
                    );
                }
                $this->orm->persist($applicationChild);
            } else if ($applicationChild !== null) {
                $this->orm->remove($applicationChild);
            }
        }

        if ($flush) {
            $draft->refreshDiscount();
            $draft->recalculatePrice();
            $this->orm->persist($draft);
            $this->orm->flush();
        }
    }

    public function upsertAddons(Application $draft, array $addonsData, bool $flush = false): void
    {
        $filteredAddons = [];
        $eventAddons = [];
        foreach ($draft->event->addons as $addon) {
            $filteredAddons[$addon->id] = isset($addonsData[$addon->id]) ? (int)$addonsData[$addon->id] : 0;
            $eventAddons[$addon->id] = $addon;
        }

        foreach ($draft->addons->get()->findBy(['id!=' => array_keys($filteredAddons)]) as $addonToDelete) {
            $this->orm->remove($addonToDelete);
        }

        foreach ($draft->addons as $addon) {
            $addon->amount = isset($filteredAddons[$addon->addon->id]) ? $filteredAddons[$addon->addon->id] : 0;
            $addon->price = $eventAddons[$addon->id]->price;
            unset($filteredAddons[$addon->addon->id]);
            $this->orm->persist($addon);
        }

        foreach ($filteredAddons as $addonId => $amount) {
            $newAddon = new ApplicationAddon($eventAddons[$addonId], $draft);
            $newAddon->amount = $amount;
            $this->orm->persist($newAddon);
        }

        if ($flush) {
            $draft->refreshDiscount();
            $draft->recalculatePrice();
            $this->orm->persist($draft);
            $this->orm->flush();
        }
    }
}
