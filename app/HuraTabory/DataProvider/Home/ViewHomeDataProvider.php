<?php
declare(strict_types=1);

namespace HuraTabory\DataProvider\Home;

use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Event\Transformer\EventDetailTransformer;
use HuraTabory\API\V1\Review\Transformer\ReviewToArrayTransformer;
use HuraTabory\Domain\Game\GameRepository;
use HuraTabory\Domain\Homepage\HomepageConfig;
use HuraTabory\Domain\Homepage\HomepageRepository;
use HuraTabory\Domain\Website\CustomJavascript;
use HuraTabory\Domain\Website\Menu;
use HuraTabory\Domain\Website\WebsiteRepository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Ebooks\Ebook;
use VCD2\Events\Event;
use VCD2\Orm;
use VCD2\Users\User;

class ViewHomeDataProvider
{
    /** @var Orm */
    private $orm;

    /** @var EventDetailTransformer */
    private $eventDetailTransformer;

    /** @var ReviewToArrayTransformer */
    private $reviewToArrayTransformer;

    /** @var HomepageRepository */
    private $homepageRepository;

    /** @var WebsiteRepository */
    private $websiteRepository;

    /** @var GameRepository */
    private $gameRepository;

    public function __construct(
        Orm $orm,
        EventDetailTransformer $eventDetailTransformer,
        ReviewToArrayTransformer $reviewToArrayTransformer,
        HomepageRepository $homepageRepository,
        WebsiteRepository $websiteRepository,
        GameRepository $gameRepository
    ) {
        $this->orm = $orm;
        $this->eventDetailTransformer = $eventDetailTransformer;
        $this->reviewToArrayTransformer = $reviewToArrayTransformer;
        $this->homepageRepository = $homepageRepository;
        $this->websiteRepository = $websiteRepository;
        $this->gameRepository = $gameRepository;
    }

    public function getData(?User $user = null, bool $isApi = true): array
    {
        $homepageConfig = $this->homepageRepository->getHomepageConfig();
        $websiteConfig = $this->websiteRepository->getWebsiteConfig();

        $eventType = [
            Event::TYPE_CAMP => 'camps',
            Event::TYPE_CAMP_SPRING => 'camps',
            Event::TYPE_TRIP => 'trips',
        ];
        $events = ['camps' => [], 'trips' => []];
        $homepageEvents = [];
        foreach ($this->orm->events->findUpcoming() as $event) {
            $eventData = $this->eventDetailTransformer->transform($event, $user ?? null, $isApi);
            $events[$eventType[$event->type]][] = $eventData;
            if (count($homepageEvents) < 3) {
                unset($eventData['capacity']);
                unset($eventData['addons']);
                unset($eventData['content']);
                $homepageEvents[] = $eventData;
            }
        }
        $archiveEvents = [];
        $url = [
            Event::TYPE_CAMP => '/tabor/',
            Event::TYPE_CAMP_SPRING => '/tabor/',
            Event::TYPE_TRIP => '/vylet/',
        ];

        if ($homepageConfig->isSectionEnabled(HomepageConfig::SECTION_ARCHIVE_EVENTS)) {
            foreach ($this->orm->events->findArchivedRandom()->limitBy(3) as $event) {
                $archiveEvents[] = [
                    'id' => $event->id,
                    'name' => $event->name,
                    'url' => $url[$event->type] . $event->slug,
                    'banner' => $event->bannerSmall,
                    'description' => $event->description,
                ];
            }
        }

        $nextEvent = null;
        $nextEventTime = null;
        if ($homepageConfig->isSectionEnabled(HomepageConfig::SECTION_NEXT_EVENT)) {
            $nextEvent = $this->orm->events->findUpcoming(null, false, true)->orderBy('starts')->limitBy(1)->fetch();
            if ($nextEvent instanceof Event) {
                $nextEvent = [
                    'name' => $nextEvent->name,
                    'url' => $url[$nextEvent->type] . $nextEvent->slug,
                    'starts' => $nextEvent->starts->format('c'),
                ];
            }
        }

        $reviewsData = [];
        if ($homepageConfig->isSectionEnabled(HomepageConfig::SECTION_REVIEWS)) {
            $reviews = $this->orm->reviews->findRandom()->limitBy(2);
            $i = 1;
            foreach ($reviews as $review) {
                $reviewsData[] = $this->reviewToArrayTransformer->transform($review, $i);
                $i++;
            }
        }

        $gamesData = [];
        foreach ($this->gameRepository->findAll() as $game) {
            $gamesData[] = [
                'id' => $game->getId(),
                'name' => $game->getName(),
                'slug' => $game->getSlug(),
                'bannerSmall' => $game->getBannerSmall(),
                'bannerLarge' => $game->getBannerLarge(),
                'descriptionShort' => $game->getDescriptionShort(),
                'descriptionLong' => $game->getDescriptionLong(),
                'isVisibleOnHomepage' => $game->isVisibleOnHomepage(),
            ];
        }

        $ebooksGroupsData = [];
        $ebooksGroupData = [];
        foreach ($this->orm->ebooks->findBy(['visible' => 1])->orderBy('position', ICollection::ASC) as $ebook) {
            /** @var Ebook $ebook */
            $ebooksGroupData[] = [
                'id' => $ebook->id,
                'name' => $ebook->name,
                'banner' => $ebook->image,
                'description' => $ebook->description,
            ];
            if (count($ebooksGroupData) === 2) {
                $ebooksGroupsData[] = $ebooksGroupData;
                $ebooksGroupData = [];
            }
        }

        $menuCollection = $websiteConfig->getMenuCollection();
        $javascripts = [];
        foreach ($websiteConfig->getCustomJavascripts() as $customJavascript) {
            if ($customJavascript->isVisibleFor($user)) {
                $javascripts[] = [
                    'id' => $customJavascript->getId(),
                    'code' => $customJavascript->getCode(),
                ];
            }
        }

        return [
            'initialLoadFinished' => true,
            'website' => [
                'name' => $websiteConfig->getName(),
                'title' => $websiteConfig->getTitle(),
                'heading' => $websiteConfig->getHeading(),
                'slogan' => $websiteConfig->getSlogan(),
                'description' => $websiteConfig->getDescription(),
                'keywords' => $websiteConfig->getKeywords(),
                'email' => $websiteConfig->getEmail(),
                'phone' => $websiteConfig->getPhone(),
                'phoneHumanReadable' => str_replace('-', ' ', str_replace('+420', '', $websiteConfig->getPhone())),
                'bankAccount' => $websiteConfig->getBankAccount(),
                'facebookLink' => $websiteConfig->getFacebookLink(),
                'instagramLink' => $websiteConfig->getInstagramLink(),
                'pinterestLink' => $websiteConfig->getPinterestLink(),
                'address' => $websiteConfig->getAddress(),
                'addressLink' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($websiteConfig->getAddress()),
                'termsAndConditions' => $websiteConfig->getTermsAndConditions(),
                'gdpr' => $websiteConfig->getGdpr(),
                'rules' => $websiteConfig->getRules(),
                'contactPerson' => $websiteConfig->getContactPerson(),
                'ico' => $websiteConfig->getIco(),
                'bankName' => $websiteConfig->getBankName(),
                'orgDescription' => $websiteConfig->getOrgDescription(),
                'google' => [
                    'appId' => $websiteConfig->getGoogleConfig()->getAppId(),
                ],
                'facebook' => [
                    'appId' => $websiteConfig->getFacebookConfig()->getAppId(),
                ],
                'menu' => [
                    Menu::MENU_TOP => $this->transformMenu($menuCollection->fetchMenu(Menu::MENU_TOP)),
                    Menu::MENU_MAIN => $this->transformMenu($menuCollection->fetchMenu(Menu::MENU_MAIN)),
                    Menu::MENU_MOBILE => $this->transformMenu($menuCollection->fetchMenu(Menu::MENU_MOBILE)),
                    Menu::MENU_FOOTER => $this->transformMenu($menuCollection->fetchMenu(Menu::MENU_FOOTER)),
                ],
                'javascripts' => $javascripts,
            ],
            'homepage' => [
                'enabledSections' => $homepageConfig->getEnabledSections(),
                'events' => $homepageEvents,
                'reviews' => $reviewsData,
                'fairytales' => [], // todo $ebooksGroupsData,
                'nextEvent' => $nextEvent,
                'archiveEvents' => $archiveEvents,
            ],
            'events' => $events,
            'loadedEvents' => [],
            'loadedStaticPages' => [],
            'games' => $gamesData,
        ];
    }

    private function transformMenu(Menu $menu): array
    {
        $data = [];
        foreach ($menu->getItems() as $item) {
            $data[] = [
                'id' => $item->getId(),
                'url' => $item->getUrl(),
                'text' => $item->getText(),
                'isExternal' => $item->isExternal(),
            ];
        }

        return $data;
    }
}
