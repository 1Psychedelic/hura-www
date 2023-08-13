<?php
declare(strict_types=1);

namespace VCD2\FacebookImages\Service;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nextras\Orm\Collection\ICollection;
use VCD2\FacebookImages\DTO\FacebookImage;
use VCD2\Orm;

class FacebookImages
{
    public const CACHE_TAG = 'facebook.images';

    /** @var Orm */
    private $orm;

    /** @var Cache */
    private $cache;

    public function __construct(Orm $orm, IStorage $storage)
    {
        $this->orm = $orm;
        $this->cache = new Cache($storage, 'fb.images');
    }

    /**
     * @param string $path
     * @return FacebookImage[]
     */
    public function getImages(string $path): array
    {
        return $this->cache->load($path, function (&$dependencies) use ($path) {
            $dependencies[Cache::TAGS] = [self::CACHE_TAG];
            $dtos = [];
            $images = $this->orm->facebookImages->findBy(['url' => $path])->orderBy('position', ICollection::ASC);
            foreach ($images as $image) {
                $dtos[] = new FacebookImage($image->image, $image->width, $image->height);
            }

            if (count($dtos) === 0) {
                $homeImages = $this->orm->facebookImages->findBy(['url' => '/'])->orderBy('position', ICollection::ASC);
                foreach ($homeImages as $image) {
                    $dtos[] = new FacebookImage($image->image, $image->width, $image->height);
                }
            }

            return $dtos;
        });
    }
}
