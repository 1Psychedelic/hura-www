<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Website;

class Menu
{
    public const MENU_TOP = 'top';
    public const MENU_MAIN = 'main';
    public const MENU_MOBILE = 'mobile';
    public const MENU_FOOTER = 'footer';

    /** @var MenuItem[] */
    private $items = [];

    public function addItem(MenuItem $item): void
    {
        $this->items[] = $item;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
