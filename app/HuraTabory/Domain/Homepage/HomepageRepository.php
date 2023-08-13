<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Homepage;

use PDO;

class HomepageRepository
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getHomepageConfig(): HomepageConfig
    {
        $result = $this->pdo->query('SELECT enabled_sections FROM vcd_homepage_config LIMIT 1');

        $enabledSections = $result->fetchColumn();

        return new HomepageConfig($enabledSections === '' ? [] : explode(',', $enabledSections));
    }

    public function saveHomepageConfig(HomepageConfig $homepageConfig): void
    {
        $stmt = $this->pdo->prepare('UPDATE vcd_homepage_config SET enabled_sections = :sections');
        $stmt->bindValue(':sections', implode(',', $homepageConfig->getEnabledSections()));
        $stmt->execute();
    }
}
