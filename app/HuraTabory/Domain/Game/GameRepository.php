<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Game;

use Generator;
use PDO;

class GameRepository
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return Generator|Game[]
     */
    public function findAll(): Generator
    {
        $stmt = $this->pdo->query('SELECT * FROM vcd_game WHERE visible = 1 ORDER BY position ASC');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $game = new Game();

            $game->setId((int)$row['id']);
            $game->setName($row['name']);
            $game->setSlug($row['slug']);
            $game->setVisibleOnHomepage((bool)$row['visible_on_homepage']);
            $game->setBannerSmall($row['banner_small']);
            $game->setBannerLarge($row['banner_large']);
            $game->setDescriptionShort($row['description_short']);
            $game->setDescriptionLong($row['description_long']);

            yield $game;
        }
    }
}
