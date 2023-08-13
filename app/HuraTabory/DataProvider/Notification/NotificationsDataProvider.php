<?php
declare(strict_types=1);

namespace HuraTabory\DataProvider\Notification;


use PDO;
use VCD\Notifications\Notifications;
use VCD2\Users\User;

class NotificationsDataProvider
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getData(?User $user = null): array
    {
        if ($user === null || !$user->isAdmin()) {
            return [
                'countNew' => 0,
                'autoRefresh' => false,
            ];
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(id) FROM vcd_notification WHERE is_read = 0 AND recipient = :recipient');
        $stmt->bindValue(':recipient', $user->id);
        $stmt->execute();
        $countNew = (int)$stmt->fetchColumn();

        return [
            'countNew' => $countNew,
            'autoRefresh' => true,
        ];
    }
}
