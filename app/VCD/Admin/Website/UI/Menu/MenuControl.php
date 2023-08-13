<?php

namespace VCD\Admin\Website\UI;

use HuraTabory\Domain\Website\Menu;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Psr\Container\ContainerInterface;

class MenuControl extends Control
{
    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->onAnchor[] = function () {
            $sql = <<<SQL
SELECT i.id AS id,i.url AS url, i.text AS text, m.key AS `key`, i.visible AS visible, i.position AS position
FROM vcd_menu_item i
LEFT JOIN vcd_menu m ON m.id = i.menu
ORDER BY position ASC
SQL;

            $result = $this->db()->query($sql);
            $collection = [
                Menu::MENU_TOP => [],
                Menu::MENU_MAIN => [],
                Menu::MENU_MOBILE => [],
                Menu::MENU_FOOTER => [],
            ];
            foreach ($result as $row) {
                $collection[$row['key']][] = $row;
            }

            $this->template->collection = $collection;
        };
    }

    function handleUp($id) {
        $row = $this->db()->table('vcd_menu_item')->wherePrimary($id)->fetch();
        $prev = $this->db()->table('vcd_menu_item')->where('menu', $row['menu'])->where('position < ?', $row['position'])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_menu_item')->wherePrimary($prev['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_menu_item')->wherePrimary($id)->update(['position' => $prev['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function handleDown($id) {
        $row = $this->db()->table('vcd_menu_item')->wherePrimary($id)->fetch();
        $next = $this->db()->table('vcd_menu_item')->where('menu', $row['menu'])->where('position > ?', $row['position'])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_menu_item')->wherePrimary($next['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_menu_item')->wherePrimary($id)->update(['position' => $next['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }
}
