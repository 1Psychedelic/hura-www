<?php
declare(strict_types=1);

namespace HuraTabory\Domain\StaticPage;

use PDO;

class StaticPageRepository
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getStaticPage(string $slug): ?StaticPage
    {
        $stmt = $this->pdo->prepare('SELECT * FROM vcd_page WHERE slug = :slug');
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new StaticPage(
            $row['slug'],
            $row['name'],
            $row['keywords'],
            $row['content']
        );
    }
}
