<?php
declare(strict_types=1);

namespace VCD\Admin;

use Nette\Database\Context;
use Nette\Security\User;
use Nette\Utils\Json;

class SavedFilters
{
    /** @var User */
    private $user;

    /** @var Context */
    private $db;

    public function __construct(User $user, Context $db)
    {
        $this->user = $user;
        $this->db = $db;
    }

    public function getFilters(string $group): array
    {
        return $this->db->table('vcd_admin_filter')->where([
            'group' => $group,
            'user' => $this->user->id,
        ])->fetchPairs('id', 'name');
    }

    public function getFilter(int $id): array
    {
        $row = $this->db->table('vcd_admin_filter')->where([
            'id' => $id,
            'user' => $this->user->id,
        ])->fetch();

        if ($row) {
            return Json::decode($row['filter'], \JSON_OBJECT_AS_ARRAY);
        }

        return [];
    }

    public function saveFilter(string $group, string $name, array $filters): int
    {
        $row = $this->db->table('vcd_admin_filter')->insert([
            'filter' => Json::encode($filters),
            'group' => $group,
            'name' => $name,
            'user' => $this->user->id,
        ]);

        return (int)$row->getPrimary();
    }

    public function deleteFilter(int $id): void
    {
        $this->db->table('vcd_admin_filter')->where(['user' => $this->user->id, 'id' => $id])->delete();
    }
}
