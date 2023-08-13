<?php

use Hafo\DI\Container;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use VCD\UI\AuthModule\CompleteSignupPresenter;
use VCD\UI\AuthModule\LoginPresenter;
use VCD\UI\AuthModule\RestorePasswordPresenter;
use VCD\UI\AuthModule\SignupPresenter;
use VCD\UI\FrontModule\ApplicationsModule\ApplicationPresenter;
use VCD\UI\FrontModule\BlogModule\BlogPresenter;
use VCD\UI\FrontModule\BlogModule\PostPresenter;
use VCD\UI\FrontModule\EventsModule\ArchivePresenter;
use VCD\UI\FrontModule\EventsModule\EventPresenter;
use VCD\UI\FrontModule\GalleryModule\LostFoundPresenter;
use VCD\UI\FrontModule\GalleryModule\PhotosPresenter;
use VCD\UI\FrontModule\UserModule\AddPasswordPresenter;
use VCD\UI\FrontModule\UserModule\ApplicationPresenter as UserApplicationPresenter;
use VCD\UI\FrontModule\UserModule\ChangePasswordPresenter;
use VCD\UI\FrontModule\WebModule\ClickPresenter;
use VCD\UI\FrontModule\WebModule\ContactPresenter;
use VCD\UI\FrontModule\WebModule\EbooksPresenter;
use VCD\UI\FrontModule\WebModule\LeadersPresenter;
use VCD\UI\FrontModule\WebModule\PagePresenter;
use VCD\UI\FrontModule\WebModule\RecruitmentsPresenter;
use VCD\UI\FrontModule\WebModule\ReviewsPresenter;
use VCD\UI\FrontModule\WebModule\SitemapPresenter;

return [

    RouteList::class => function(RouteList $router, Container $c) {
        $prefix = '';

        /*$router[] = new Route('//xn--vd-ema.eu[<anything .*+>]', function($anything = '') use ($c) {
            if(\Nette\Utils\Strings::startsWith($anything, '/')) {
                $anything = substr($anything, 1);
            }
            if(\Nette\Utils\Strings::endsWith($anything, '/')) {
                $anything = substr($anything, 0, -1);
            }
            $db = $c->get(\Nette\Database\Context::class);
            $row = $db->table('vcd_short_url')->where('path', $anything)->fetch();
            $url = $row ? $row['url'] : 'https://volnycasdeti.cz';
            $c->get(\Nette\Http\IResponse::class)->redirect(
                $url,
                \Nette\Http\IResponse::S302_FOUND
            );
        });*/

        /*$router[] = new Route($prefix . '/admin/api[/<action>[/<id>]]', [
            'module' => 'Admin',
            'presenter' => 'Api',
            'action' => 'default'
        ]);*/
        $router[] = new Route($prefix . '/admin[/<action>[/<id>]]', [
            'module' => 'Admin',
            'presenter' => 'Admin',
            'action' => 'default'
        ]);

/*
        $router[] = new Route($prefix . '/letni-tabory-2018', function() use ($c) {
            $c->get(\Nette\Http\IResponse::class)->redirect(
                $c->get('base') . '/letni-tabory-2020',
                \Nette\Http\IResponse::S301_MOVED_PERMANENTLY
            );
        });
        $router[] = new Route($prefix . '/letni-tabory-2019', function() use ($c) {
            $c->get(\Nette\Http\IResponse::class)->redirect(
                $c->get('base') . '/letni-tabory-2020',
                \Nette\Http\IResponse::S301_MOVED_PERMANENTLY
            );
        });
        $router[] = new Route($prefix . '/letni-tabory', function() use ($c) {
            $c->get(\Nette\Http\IResponse::class)->redirect(
                $c->get('base') . '/letni-tabory-2020',
                \Nette\Http\IResponse::S301_MOVED_PERMANENTLY
            );
        });
        $router[] = new Route($prefix . '/tabory', [
            'module' => 'Front:Events',
            'presenter' => 'Events',
            'action' => 'camps',
        ]);
        $router[] = new Route($prefix . '/<type letni-tabory-2020|vylety|jarni-tabory>', [
            'module' => 'Front:Events',
            'presenter' => 'Events',
            'action' => 'default',
            'type' => [
                Route::FILTER_TABLE => [
                    'letni-tabory-2020' => \VCD2\Events\Event::TYPE_CAMP,
                    'vylety' => \VCD2\Events\Event::TYPE_TRIP,
                    'jarni-tabory' => \VCD2\Events\Event::TYPE_CAMP_SPRING,
                ]
            ]
        ]);
        $router[] = new Route($prefix . '/fotky[/<id>]', [
            'module' => 'Front:Gallery',
            'presenter' => 'Photos',
            'action' => 'default'
        ]);
        $router[] = new Route($prefix . '/ztraty-a-nalezy[/<id>]', [
            'module' => 'Front:Gallery',
            'presenter' => 'LostFound',
            'action' => 'default'
        ]);
        $router[] = new Route($prefix . '/prihlaska/<_event>[/<action>[/<id>]]', [
            'module' => 'Front:Applications',
            'presenter' => 'Application',
            'action' => [
                Route::VALUE => 'default',
                Route::FILTER_TABLE => [
                    'zakonny-zastupce' => 'parent',
                    'dite' => 'child',
                    'deti' => 'children',
                    'smazat' => 'child-delete',
                    'dokonceni' => 'finish',
                    'navrat-z-platebni-brany' => 'returnFromGateway',
                    'extra' => 'step',
                ]
            ]
        ]);
        $router[] = new Route($prefix . '/udalost/<_event>[/<tab>]', [
            'module' => 'Front:Events',
            'presenter' => 'Event',
            'action' => 'default'
        ]);
        $router[] = new Route($prefix . '/posta[/<id>]', [
            'module' => 'Front:User',
            'presenter' => 'PostOffice',
            'action' => 'default',
        ]);
        $router[] = new Route($prefix . '/profil[/<action>[/<id>]]', [
            'module' => 'Front:User',
            'presenter' => 'Profile',
            'action' => [
                Route::VALUE => 'profil',
                Route::FILTER_TABLE => [
                    'moje-udaje' => 'parent',
                    'dite' => 'child',
                    'profil' => 'default'
                ],
            ],
        ]);

        $router[] = new Route($prefix . '/gopay/<action>', [
            'module' => 'Front:Web',
            'presenter' => 'GoPay',
        ]);

        $table = [
            'vedouci-a-lektori' => LeadersPresenter::LINK_DEFAULT,
            'prihlaseni' => LoginPresenter::LINK_DEFAULT,
            //'udalost' => EventPresenter::LINK_DEFAULT,
            'kontakty' => ContactPresenter::LINK_DEFAULT,
            'ztraty-a-nalezy' => LostFoundPresenter::LINK_DEFAULT,
            'fotky' => PhotosPresenter::LINK_DEFAULT,
            'recenze' => ReviewsPresenter::LINK_DEFAULT,
            'pridat-heslo' => AddPasswordPresenter::LINK_DEFAULT,
            'zmenit-heslo' => ChangePasswordPresenter::LINK_DEFAULT,
            'obnovit-heslo' => RestorePasswordPresenter::LINK_DEFAULT,
            'nastavit-heslo' => RestorePasswordPresenter::LINK_RESTORE,
            'registrace' => SignupPresenter::LINK_DEFAULT,
            'stranka' => PagePresenter::LINK_DEFAULT,
            'moje-prihlaska' => UserApplicationPresenter::LINK_DEFAULT,
            'archiv' => ArchivePresenter::LINK_DEFAULT,
            'mapa-webu' => SitemapPresenter::LINK_DEFAULT,
            'blog' => BlogPresenter::LINK_DEFAULT,
            'clanek' => PostPresenter::LINK_DEFAULT,
            'e-booky-pro-deti-ke-stazeni-zdarma' => EbooksPresenter::LINK_DEFAULT,
            'stahnout-ebook' => EbooksPresenter::LINK_DOWNLOAD,
            'pridej-se-k-nam' => RecruitmentsPresenter::LINK_DEFAULT,
            'click' => ClickPresenter::LINK_DEFAULT,
            'aktivovat-ucet' => CompleteSignupPresenter::LINK_DEFAULT,
        ];
        foreach($table as $url => $link) {
            $router[] = new Route($prefix . '/' . $url . '[/<id>[/<tab>]]', substr($link, 1));
        }

        $router[] = new Route($prefix . '/<page tabor|vylet>/<id>', [
            'module' => 'Front:Web',
            'presenter' => 'OldSiteMapping',
            'action' => 'default'
        ]);
        $router[] = new Route($prefix . '/web.front.gallery/gallery/<id>', [
            'module' => 'Front:Web',
            'presenter' => 'OldSiteMapping',
            'action' => 'default',
            'page' => 'fotky'
        ]);

        // todo split
        $router[] = new Route($prefix . '/[<module>[/<presenter>[/<id>[/<tab>]]]]', [
            'module' => [
                Route::VALUE => 'Front:Homepage',
                Route::FILTER_TABLE => []
            ],
            'presenter' => [
                Route::VALUE => 'Homepage',
                Route::FILTER_TABLE => [
                    'vedouci-a-lektori' => 'leaders',
                    'prihlaseni' => 'login',
                    'udalost' => 'event',
                    'kontakty' => 'contact',
                    'ztraty-a-nalezy' => 'lostAndFound',
                    'online-z-akce' => 'online',
                    'fotky' => 'photos',
                    'recenze' => 'reviews',
                    'nastavit-heslo' => 'setPassword',
                    'pridat-heslo' => 'addPassword',
                    'zmenit-heslo' => 'changePassword',
                    'obnovit-heslo' => 'restorePassword',
                    'registrace' => 'signup',
                    'prihlaska' => 'application',
                    'stranka' => 'page',
                    'profil' => 'profile',
                    'moje-prihlaska' => 'myApplication',
                    'archiv' => 'archive',
                    'mapa-webu' => 'sitemap',
                    'clanek' => 'blogPost',
                    'e-booky-pro-deti-ke-stazeni-zdarma' => 'ebooks',
                    'pridej-se-k-nam' => 'recruitments',
                ]
            ],
            'action' => 'default'
        ]);





        /*
        $router[] = new Route($prefix . '/admin[/<action>[/<id>]]', [
            'presenter' => 'Admin',
            'action' => 'default'
        ]);

        $router[] = new Route($prefix . '/v2[/<module>[/<presenter>[/<action>]]]', [
            'module' => 'Front:Homepage',
            'presenter' => 'Default',
            'action' => 'default'
        ]);

        $router[] = new Route($prefix . '/tabory', function() use ($c) {
            $c->get(\Nette\Http\IResponse::class)->redirect(
                $c->get('base') . '/letni-tabory-2017',
                \Nette\Http\IResponse::S302_FOUND
            );
        });
        $router[] = new Route($prefix . '/<type letni-tabory-2017|vylety>', [
            'presenter' => 'Front',
            'action' => 'events',
            'type' => [
                Route::FILTER_TABLE => [
                    'letni-tabory-2017' => VCD\Events\EventItem::TYPE_CAMP,
                    'vylety' => VCD\Events\EventItem::TYPE_TRIP
                ]
            ]
        ]);
        $router[] = new Route($prefix . '/fotky[/<id>]', [
            'presenter' => 'Front',
            'action' => 'photos'
        ]);
        $router[] = new Route($prefix . '/ztraty-a-nalezy[/<id>]', [
            'presenter' => 'Front',
            'action' => 'lostFound'
        ]);
        $router[] = new Route($prefix . '/prihlaska/<id>[/<step>[/<extra>]]', [
            'presenter' => 'Front',
            'action' => 'application',
            'step' => [
                Route::FILTER_TABLE => [
                    'zakonny-zastupce' => 1,
                    'dite' => 2,
                    'deti' => 3,
                    'smazat' => 4,
                    'dokonceni' => 5,
                    'dokonceno' => 6,
                    'extra' => 7
                ]
            ]
        ]);
        $router[] = new Route($prefix . '/profil[/<id>]', [
            'presenter' => 'Front',
            'action' => 'profile',
            'id' => [
                Route::FILTER_TABLE => [
                    'moje-udaje' => 'parent',
                    'profil' => 'profile'
                ]
            ]
        ]);

        $router[] = new Route($prefix . '/<page tabor|vylet>/<id>', [
            'presenter' => 'Front',
            'action' => 'old'
        ]);
        $router[] = new Route($prefix . '/web.front.gallery/gallery/<id>', [
            'presenter' => 'Front',
            'action' => 'old',
            'page' => 'fotky'
        ]);

        $router[] = new Route($prefix . '/[<action>[/<id>[/<tab>]]]', [
            'presenter' => 'Front',
            'action' => [
                Route::VALUE => 'homepage',
                Route::FILTER_TABLE => [
                    'vedouci-a-lektori' => 'leaders',
                    'prihlaseni' => 'login',
                    'udalost' => 'event',
                    'kontakty' => 'contact',
                    'ztraty-a-nalezy' => 'lostAndFound',
                    'online-z-akce' => 'online',
                    'fotky' => 'photos',
                    'recenze' => 'reviews',
                    'nastavit-heslo' => 'setPassword',
                    'pridat-heslo' => 'addPassword',
                    'zmenit-heslo' => 'changePassword',
                    'obnovit-heslo' => 'restorePassword',
                    'registrace' => 'signup',
                    'prihlaska' => 'application',
                    'stranka' => 'page',
                    'profil' => 'profile',
                    'moje-prihlaska' => 'myApplication',
                    'archiv' => 'archive',
                    'mapa-webu' => 'sitemap',
                    'clanek' => 'blogPost',
                    'e-booky-pro-deti-ke-stazeni-zdarma' => 'ebooks',
                    'pridej-se-k-nam' => 'recruitments',
                ]
            ]
        ]);*/
        return $router;
    },


];
