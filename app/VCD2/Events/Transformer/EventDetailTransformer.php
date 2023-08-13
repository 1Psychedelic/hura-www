<?php
declare(strict_types=1);

namespace VCD2\Events\Transformer;

use DateTimeImmutable;
use VCD2\Events\Event;

class EventDetailTransformer
{
    public function transform(Event $event) {
        $url = [
            Event::TYPE_CAMP => '/tabor/',
            Event::TYPE_CAMP_SPRING => '/tabor/',
            Event::TYPE_TRIP => '/vylet/',
        ];

        return [
            'id' => $event->id,
            'name' => $event->name,
            'url' => $url[$event->type] . $event->slug,
            'banner' => $event->bannerSmall,
            'bannerLarge' => $event->bannerLarge,
            'description' => $event->description,
            'date' => $this->buildTerm($event->starts, $event->ends, true),
            'age' => $event->ageMin . ' – ' . $event->ageMax . ' let',
            'price' => $event->price,
            'priceText' => number_format($event->price, 0, '.', ' ') . ' Kč',
            'capacity' => $event->countFreeSlots() . ' z ' . $event->maxParticipants,
            'addons' => $this->transformAddons(),
            'content' => $this->transformContent($event),
            'isArchived' => $event->isArchived,
            'hasOpenApplications' => $event->hasOpenApplications,
            'ageMin' => $event->ageMin,
            'ageMax' => $event->ageMax,
            'ageCap' => $event->ageCap,
            'starts' => $event->starts->format('Y-m-d H:i:s'),
            'ends' => $event->ends->format('Y-m-d H:i:s'),
        ];
    }

    private function transformContent(Event $event) {
        $data = [];
        $isFirst = true;
        foreach ($event->tabs as $tab) {
            $data[] = [
                'tab' => $tab->name,
                'slug' => $isFirst ? '' : $tab->slug,
                'content' => $tab->content,
            ];
            $isFirst = false;
        }

        return $data;
    }

    private function transformAddons() {
        return [
            [
                'id' => 1,
                'icon' => '/images/icons/addons/icon-bus.png',
                'name' => 'Doprava',
                'description' => 'Autobusová doprava z Brna na základnu a zase zpět.',
                'link' => [
                    'url' => '#',
                    'text' => 'více informací zde',
                ],
                'price' => 300,
                'priceText' => '+300 Kč/dítě',
            ],
            [
                'id' => 2,
                'icon' => '/images/icons/addons/icon-insurance.png',
                'name' => 'Pojištění',
                'description' => 'Úrazové pojištění<br>Pojištění storna<br>Pojištění odpovědnosti',
                'link' => [
                    'url' => '#',
                    'text' => 'více informací zde',
                ],
                'price' => 155,
                'priceText' => '+155 Kč/dítě',
            ],
            [
                'id' => 3,
                'icon' => '/images/icons/addons/icon-antigluten.png',
                'name' => 'Bezlepková dieta',
                'description' => 'Plnohodnotná bezlepková dieta po celou dobu pobytu pro vaše dítě.',
                'link' => null,
                'price' => 300,
                'priceText' => '+300 Kč/dítě',
            ],
            [
                'id' => 4,
                'icon' => '/images/icons/addons/icon-voucher.png',
                'name' => 'Dárkový poukaz',
                'description' => 'Graficky zpracovaný poukaz pro vaše dítě ve formátu PDF.',
                'link' => [
                    'url' => '#',
                    'text' => 'ukázka zde',
                ],
                'price' => 50,
                'priceText' => '+50 Kč/dítě',
            ]
        ];
    }

    private function buildTerm(DateTimeImmutable $starts, DateTimeImmutable $ends, bool $monthAsNumber) {

        if ($monthAsNumber) {
            $format = 'j.';
            if ($starts->format('n') !== $ends->format('n')) {
                $format .= ' n.';
            }
            if ($starts->format('Y') !== $ends->format('Y')) {
                $format .= ' Y';
            }
            if ($starts->format('j n Y') !== $ends->format('j n Y')) {
                return $starts->format($format) . ' – ' . $ends->format('j. n. Y');
            }
            return $ends->format('j. n. Y');
        }

        $format = '%e.';
        if ($starts->format('n') !== $ends->format('n')) {
            $format .= ' %B';
        }
        if ($starts->format('Y') !== $ends->format('Y')) {
            $format .= ' %Y';
        }
        if ($starts->format('j n Y') !== $ends->format('j n Y')) {
            return $starts->format($format) . ' – ' . $ends->format('%e. %B %Y');
        }
        return $ends->format('%e. %B %Y');
    }
}
