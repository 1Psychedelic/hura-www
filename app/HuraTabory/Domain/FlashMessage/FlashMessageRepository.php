<?php
declare(strict_types=1);

namespace HuraTabory\Domain\FlashMessage;

use Nette\Utils\Random;
use PDO;
use PDOException;

class FlashMessageRepository
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find(string $hash): ?FlashMessage
    {
        $stmt = $this->pdo->prepare('SELECT id,type,message FROM system_flash_message WHERE read_at IS NULL AND hash = :hash');
        $stmt->bindValue(':hash', $hash);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($row === false) {
            return null;
        }

        $stmt = $this->pdo->prepare('UPDATE system_flash_message SET read_at = NOW() WHERE id = :id');
        $stmt->bindValue(':id', $row['id']);
        $stmt->execute();
        $stmt->closeCursor();

        return new FlashMessage((string)$row['type'], (string)$row['message']);
    }

    public function create(string $type, string $message): string
    {
        $created = false;
        $stmt = $this->pdo->prepare('INSERT INTO system_flash_message (hash, type, message) VALUES (:hash, :type, :message)');
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':message', $message);
        do {
            $hash = Random::generate(8, '0-9a-zA-Z');
            $stmt->bindValue(':hash', $hash);
            try {
                $stmt->execute();
                $created = true;
            } catch (PDOException $e) {
                if ((int)$e->getCode() !== 23000) {
                    throw $e;
                }
            }
        } while (!$created);

        $stmt->closeCursor();

        return $hash;
    }
}
