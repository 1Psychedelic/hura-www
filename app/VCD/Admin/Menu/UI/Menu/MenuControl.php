<?php

namespace VCD\Admin\Menu\UI;

use Hafo\Admin\Menu\TopMenu\ArrayTopMenu;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Utils\Html;
use VCD\Admin\Applications\NewApplications;
use VCD\Notifications\Notifications;

class MenuControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;

        $this->onAnchor[] = function() {

            $this->template->useLegacyMenu = FALSE;

            $icon = function($icon) {
                return Html::el()->setHtml('<span class="glyphicon glyphicon-' . $icon . '"  style="font-size:70px;margin-left:-20px;margin-top:20px;margin-bottom:20px;color:#fff;"></span>');
            };
            $socicon = function($icon) {
                return Html::el()->setHtml('<span class="socicon socicon-' . $icon . '" style="font-size:70px;margin-left:-20px;margin-top:20px;margin-bottom:20px;color:#fff;"></span>');
            };
            $img = function($img) {
                return Html::el()->setHtml('<img src="' . $this->template->baseUri . '/www/assets/img/admin/' . $img . '" class="img-responsive">');
            };
            $pl = function($dest, array $args = []) {
                return $this->presenter->link($dest, $args);
            };

            $color = [
                'yellow' => '#cba81e',
                'orange' => '#dc542e',
                'blue' => '#28a9e3',
                'red' => '#b31919',
                'purple' => '#4d51f7',
                'cyan' => '#33bcb1',
                'green' => '#28b779',
                'gray' => '#aaaaaa',
                'light-red' => '#f74d4e',
            ];

            $menu = (new ArrayTopMenu)
                ->addSubMenu('Web')
                    ->addItem('Tábory a výlety', $pl('gotoEvents!'), $color['yellow'], $img('events.png'))
                    ->addItem('Hlavní stránka', $pl('homepage'), $color['orange'], $img('home.png'))
                    ->addItem('Vedoucí a lektoři', $pl('leaders'), $color['blue'], $img('users.png'))
                    ->addItem('Stránky', $pl('pages'), $color['red'], $icon('duplicate'))
                    ->addItem('E-booky', $pl('ebooks'), $color['purple'], $icon('book'))
                    ->addItem('Hry', $pl('games'), $color['blue'], $icon('pawn'))
                    ->addItem('Nábor', $pl('recruitments'), $color['yellow'], $icon('user'))
                    ->addItem('Blog', $pl('blogArticles'), $color['cyan'], $icon('pencil'))
                ->endSubMenu()
                ->addSubMenu('Lidé')
                    ->addItem('Přihlášky', $pl('gotoApplications!'), $color['green'], $img('applications.png'), $this->container->get(NewApplications::class)->count())
                    ->addItem('Faktury a platby', $pl('invoices'), $color['purple'], $icon('credit-card'))
                    ->addItem('Uživatelé', $pl('users'), $color['light-red'], $icon('user'))
                    ->addItem('Děti', $pl('children'), $color['blue'], $img('children.png'))
                    ->addItem('Ztráty a nálezy', $pl('lostFound'), $color['purple'], $img('lostfound.png'))
                    ->addItem('Svátky', $pl('nameDays'), $color['gray'], $icon('calendar'))
                ->endSubMenu()
                ->addSubMenu('Marketing')
                    ->addItem('Odběry', $pl('newsletter'), $color['light-red'], $img('users.png'))
                    ->addItem('FB obrázky', $pl('facebookImages'), $color['blue'], $socicon('facebook'))
                    ->addItem('E-maily', $pl('emails'), $color['purple'], $img('email.png'))
                    ->addItem('Slevy', $pl('discounts'), $color['red'], $img('discount.png'))
                    ->addItem('Zkracovač URL', $pl('shortUrls'), $color['green'], $icon('link'))
                ->endSubMenu()
                ->addSubMenu('Pokročilé')
                    ->addItem('Plánovač úloh', $pl('cronTasks'), $color['blue'], $icon('time'))
                    ->addItem('Monolog', $pl('monolog', ['minLevel' => 500]), $color['light-red'], $icon('exclamation-sign'))
                    ->addItem('Soubory', $pl('files'), $color['purple'], $icon('floppy-disk'))
                    ->addItem('Nastavení webu', $pl('website'), $color['gray'], $icon('cog'))
                ->endSubMenu()
                ->addSubMenu('Ostatní')
                    //->addItem('Dropbox', 'https://www.dropbox.com/home/Voln%C3%BD%20%C4%8Das%20d%C4%9Bt%C3%AD', $color['blue'], $img('dropbox.png'), NULL, NULL, TRUE)
                    ->addItem('Generátor QR kódů', $pl('qr'), $color['light-red'], $img('qr.png'))
                    ->addItem('Bodovací appka', $pl('mobileAppPoints'), $color['blue'], $icon('phone'))
                    ->addItem('Měření prokliků', $pl('clicks'), $color['purple'], $icon('link'))
                    ->addItem('Statistiky', 'https://grafana.lukasklika.cz/d/2maTW1H7k/hura-tabory-test?orgId=1', $color['blue'], $icon('dashboard'))
                    ->addItem('Tisk stránky', $pl('this', ['print' => TRUE]), $color['green'], $icon('print'))
                ->endSubMenu();
            
            $this->addComponent(new \Hafo\Admin\Menu\UI\MenuControl($menu), 'menu');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->newApplications = $this->container->get(NewApplications::class)->count();
        $this->template->render();
    }

}
