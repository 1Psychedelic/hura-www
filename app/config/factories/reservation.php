<?php
declare(strict_types=1);

use Hafo\DI\Container;

return [
    'vcd_event.addons' => function (Container $c) {
        return [
            [
                'name' => 'Doprava',
                'description' => 'Autobusová doprava z Brna na základnu a zase zpět.',
                'price' => 300,
                'enabled' => true,
                'position' => 1,
                'icon' => '/images/icons/addons/icon-bus.png',
                'linkUrl' => '#',
                'linkText' => 'více informací zde',
            ],
            [
                'name' => 'Pojištění',
                'description' => 'Úrazové pojištění<br>Pojištění storna<br>Pojištění odpovědnosti',
                'price' => 155,
                'enabled' => true,
                'position' => 2,
                'icon' => '/images/icons/addons/icon-insurance.png',
                'linkUrl' => '#',
                'linkText' => 'více informací zde',
            ],
            [
                'name' => 'Bezlepková dieta',
                'description' => 'Plnohodnotná bezlepková dieta po celou dobu pobytu pro vaše dítě.',
                'price' => 300,
                'enabled' => true,
                'position' => 3,
                'icon' => '/images/icons/addons/icon-antigluten.png',
                'linkUrl' => null,
                'linkText' => null,
            ],
            [
                'name' => 'Dárkový poukaz',
                'description' => 'Graficky zpracovaný poukaz pro vaše dítě ve formátu PDF.',
                'price' => 50,
                'enabled' => true,
                'position' => 4,
                'icon' => '/images/icons/addons/icon-voucher.png',
                'linkUrl' => '#',
                'linkText' => 'ukázka zde',
            ],
        ];
    }
];
