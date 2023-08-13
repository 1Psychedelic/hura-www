<?php
declare(strict_types=1);

namespace HuraTabory\DataProvider\Event;

use HuraTabory\API\V1\Event\Transformer\EventDetailTransformer;
use VCD2\Events\Event;
use VCD2\Orm;
use VCD2\Users\User;

class ViewEventDataProvider
{
    /** @var Orm */
    private $orm;

    /** @var EventDetailTransformer */
    private $eventDetailTransformer;

    public function __construct(Orm $orm, EventDetailTransformer $eventDetailTransformer)
    {
        $this->orm = $orm;
        $this->eventDetailTransformer = $eventDetailTransformer;
    }

    public function getData(string $path, ?User $user = null, bool $isApi = true): ?array
    {
        [, $typeSlug, $slug] = explode('/', $path);

        if (!array_key_exists($typeSlug, Event::SLUG_TO_TYPE_MAP)) {
            return null;
        }

        $condition = [
            'type' => Event::SLUG_TO_TYPE_MAP[$typeSlug],
            'slug' => $slug,
        ];

        if ($user === null || !$user->isAdmin()) {
            $condition['visible'] = true;
        }

        $event = $this->orm->events->getBy($condition);
        if ($event === null) {
            return null;
        }

        return $this->eventDetailTransformer->transform($event, $user, $isApi);
    }
}
