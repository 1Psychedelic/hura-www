<?php

namespace VCD\Admin\Website\UI;

use HuraTabory\Domain\Website\Menu;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Psr\Container\ContainerInterface;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

class MenuItemControl extends Control
{
    private $container;

    function __construct(ContainerInterface $container, $menu, $id = null)
    {
        $this->container = $container;

        $this->onAnchor[] = function () use ($id, $menu) {
            $f = new Form();
            $f->setRenderer(new AdminFormRenderer());
            $f->addText('text', 'Text odkazu')->setRequired();
            $f->addText('url', 'Url odkazu')->setRequired();
            $f->addCheckbox('is_external', 'Externí odkaz');
            $f->addCheckbox('visible', 'Viditelný');
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function (Form $f) use ($id, $menu) {
                if ($f->isSubmitted() === $f['save']) {

                    $menuId = (int)$this->db()->table('vcd_menu')->where('key', $menu)->fetchField();
                    $data = $f->getValues(TRUE);
                    if($id === NULL) {
                        $data['position'] = $this->db()->table('vcd_menu_item')->where('menu', $menuId)->order('position DESC')->limit(1)->select('position')->fetchField() + 1;
                        $data['menu'] = $menuId;
                        $this->db()->table('vcd_menu_item')->insert($data);
                    } else {
                        $this->db()->table('vcd_menu_item')->wherePrimary($id)->update($data);
                    }
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('websiteMenu');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_menu_item')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('websiteMenu');
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('vcd_menu_item')->wherePrimary($id)->fetch());
            }
            $this->addComponent($f, 'form');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }
}
