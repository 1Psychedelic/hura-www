<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Homepage;

use Hafo\Http\CacheHeaders;

class HomepageConfig
{
    public const SECTION_WHY_US = 'whyUs';
    public const SECTION_REVIEWS = 'reviews';
    public const SECTION_GAMES = 'games';
    public const SECTION_FAIRYTALES = 'fairytales';
    public const SECTION_NEXT_EVENT = 'nextEvent';
    public const SECTION_SUBSCRIBE = 'subscribe';
    public const SECTION_ARCHIVE_EVENTS = 'archiveEvents';

    public const AVAILABLE_SECTIONS = [
        self::SECTION_WHY_US => 'Proč k nám',
        self::SECTION_REVIEWS => 'Hodnocení táborů',
        self::SECTION_GAMES => 'Naše stolní hry',
        self::SECTION_FAIRYTALES => 'Naše pohádky',
        self::SECTION_NEXT_EVENT => 'Nejbližší akce začíná už za',
        self::SECTION_SUBSCRIBE => 'Přihlašte se k odběru novinek',
        self::SECTION_ARCHIVE_EVENTS => 'Příběhy z táborů',
    ];

    /** @var string[] */
    private $enabledSections = [];

    public function __construct(array $enabledSections)
    {
        $this->enabledSections = $enabledSections;
    }

    /**
     * @return string[]
     */
    public function getEnabledSections(): array
    {
        return $this->enabledSections;
    }

    public function isSectionEnabled(string $section): bool
    {
        return in_array($section, $this->enabledSections, true);
    }

    public function getCacheHeaders(): CacheHeaders
    {
        $etag = md5(implode(',', $this->enabledSections));

        return new CacheHeaders($etag);
    }
}
