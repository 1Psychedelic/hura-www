<?php
declare(strict_types=1);

namespace HuraTabory\DataProvider\StaticPage;

use HuraTabory\Domain\StaticPage\StaticPageRepository;

class ViewStaticPageDataProvider
{
    /** @var StaticPageRepository */
    private $staticPageRepository;

    public function __construct(StaticPageRepository $staticPageRepository)
    {
        $this->staticPageRepository = $staticPageRepository;
    }

    public function getData(string $slug): ?array
    {
        if ($slug === '') {
            return null;
        }

        $staticPage = $this->staticPageRepository->getStaticPage($slug);

        if ($staticPage === null) {
            return null;
        }

        return [
            'slug' => $staticPage->getSlug(),
            'name' => $staticPage->getName(),
            'keywords' => $staticPage->getKeywords(),
            'content' => $staticPage->getContent(),
        ];
    }
}
