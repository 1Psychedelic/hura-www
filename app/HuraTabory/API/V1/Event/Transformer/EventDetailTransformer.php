<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Event\Transformer;

use DateTimeImmutable;
use Hafo\DI\Container;
use VCD2\Events\Event;
use VCD2\Users\User;

class EventDetailTransformer
{
    /** @var EventTermTransformer */
    private $eventTermTransformer;

    /** @var Container */
    private $container;

    public function __construct(EventTermTransformer $eventTermTransformer, Container $container)
    {
        $this->eventTermTransformer = $eventTermTransformer;
        $this->container = $container;
    }

    public function transform(Event $event, ?User $user = null, bool $isApi = true): array
    {
        $url = [
            Event::TYPE_CAMP => '/tabor/',
            Event::TYPE_CAMP_SPRING => '/tabor/',
            Event::TYPE_TRIP => '/vylet/',
        ];

        $priceOrDiscount = $event->currentPriceOrDiscount;

        $price = $event->getCurrentPrice($user === null ? 0 : $user->vipLevel);
        $priceBeforeDiscount = $event->getCurrentPriceBeforeDiscount($user === null ? 0 : $user->vipLevel);

        $data = [
            'id' => $event->id,
            'name' => $event->name,
            'subheading' => $event->subheading,
            'url' => $url[$event->type] . $event->slug,
            'banner' => $event->bannerSmall,
            'bannerLarge' => $event->bannerLarge,
            'description' => $event->description,
            'date' => $this->eventTermTransformer->transform($event->starts, $event->ends, true),
            'age' => $event->ageMin . ' – ' . $event->ageMax . ' let',
            'price' => $price,
            'priceText' => $price === 0 ? 'Zdarma' : number_format($price, 0, '.', ' ') . ' Kč',
            'priceBeforeDiscount' => $priceBeforeDiscount,
            'priceBeforeDiscountText' => number_format($priceBeforeDiscount, 0, '.', ' ') . ' Kč',
            'allowDiscountCodes' => $priceOrDiscount->allowDiscountCodes,
            'allowCredits' => $priceOrDiscount->allowCredits,
            'discountExpiresAt' => $price === $priceBeforeDiscount || $event->currentDiscount === null ? null : $event->currentDiscount->ends->format('c'),
            'capacity' => $event->countFreeSlots() . ' z ' . $event->maxParticipants,
            'addons' => $this->transformAddons($event),
            'content' => $this->transformContent($event),
            'images' => $this->transformImages($event),
            'isArchived' => $event->isArchived,
            'hasOpenApplications' => $event->hasOpenApplications,
            'ageMin' => $event->ageMin,
            'ageMax' => $event->ageMax,
            'ageCap' => $event->ageCap,
            'starts' => $event->starts->format('Y-m-d H:i:s'),
            'ends' => $event->ends->format('Y-m-d H:i:s'),
            'sidebarHtml' => (string)$event->sidebarHtml,
        ];

        if (!$isApi) {
            $data['maxParticipants'] = $event->maxParticipants;
            $data['hasCapacity'] = $event->countFreeSlots() > 0;
            $data['applicationsCloseAt'] = $event->applicableUntil === null ? $event->starts->format('Y-m-d H:i:s') : $event->applicableUntil->format('Y-m-d H:i:s');
            $data['schema'] = [
                'location' => [
                    '@type' => 'Place',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'postalCode' => $event->schemaLocationAddressPostalCode,
                        'addressCountry' => 'CZ',
                        'addressRegion' => $event->schemaLocationAddressRegion,
                        'addressLocality' => $event->schemaLocationAddressLocality,
                    ],
                ],
            ];
        }

        return $data;
    }

    private function transformContent(Event $event): array
    {
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

    private function transformAddons(Event $event): array
    {
        $addons = [];
        foreach ($event->addons as $addon) {
            if (!$addon->enabled) {
                continue;
            }

            $addons[] = [
                'id' => $addon->id,
                'icon' => $addon->icon,
                'name' => $addon->name,
                'description' => $addon->description,
                'link' => $addon->linkUrl === null ? null : [
                    'url' => $addon->linkUrl,
                    'text' => $addon->linkText,
                ],
                'price' => $addon->price,
                'priceText' => '+' . $addon->price . ' Kč/dítě',
            ];
        }

        return $addons;
    }

    private function transformImages(Event $event): array
    {
        $images = [];
        foreach ($event->images as $image) {
            $images[] = [
                'image' => '/upload/events/' . $event->id . '/images/' . $image->name,
                'thumbnail' => '/upload/events/' . $event->id . '/images/thumb_' . $image->name,
                'thumbnailWidth' => $image->thumbW,
                'thumbnailHeight' => $image->thumbH,
                'name' => $image->name,
            ];
        }

        return $images;
    }
}
