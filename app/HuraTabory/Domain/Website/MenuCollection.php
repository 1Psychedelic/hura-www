<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Website;

class MenuCollection
{
    /** @var Menu[] */
    private $menu = [];

    public function fetchMenu(string $key): Menu
    {
        if (!isset($this->menu[$key])) {
            $this->menu[$key] = new Menu();
        }

        return $this->menu[$key];
    }
}
