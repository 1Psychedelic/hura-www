<?php

use Hafo\DI\Container;

return [

    'orm.repository' => function (Container $c, $repositoryClass, $mapperClass) {
        return new $repositoryClass(
            $c->get($mapperClass),
            $c->get(\Nextras\Orm\Repository\IDependencyProvider::class)
        );
    },

    \VCD2\Applications\Repository\ApplicationRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Applications\Repository\ApplicationRepository::class,
            \VCD2\Applications\Mapper\ApplicationMapper::class
        );
    },

    \VCD2\Applications\Repository\ChildRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Applications\Repository\ChildRepository::class,
            \VCD2\Applications\Mapper\ChildMapper::class
        );
    },

    \VCD2\Applications\Repository\StepChoiceRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Applications\Repository\StepChoiceRepository::class,
            \VCD2\Applications\Mapper\StepChoiceMapper::class
        );
    },

    \VCD2\Applications\Repository\ApplicationAddonRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Applications\Repository\ApplicationAddonRepository::class,
            \VCD2\Applications\Mapper\ApplicationAddonMapper::class
        );
    },

    \VCD2\Credits\Repository\CreditMovementRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Credits\Repository\CreditMovementRepository::class,
            \VCD2\Credits\Mapper\CreditMovementMapper::class
        );
    },

    \VCD2\Credits\Repository\CreditRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Credits\Repository\CreditRepository::class,
            \VCD2\Credits\Mapper\CreditMapper::class
        );
    },

    \VCD2\Discounts\Repository\DiscountCodeRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Discounts\Repository\DiscountCodeRepository::class,
            \VCD2\Discounts\Mapper\DiscountCodeMapper::class
        );
    },

    \VCD2\Discounts\Repository\DiscountRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Discounts\Repository\DiscountRepository::class,
            \VCD2\Discounts\Mapper\DiscountMapper::class
        );
    },

    \VCD2\Events\Repository\ApplicationStepRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Events\Repository\ApplicationStepRepository::class,
            \VCD2\Events\Mapper\ApplicationStepMapper::class
        );
    },

    \VCD2\Events\Repository\ApplicationStepOptionRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Events\Repository\ApplicationStepOptionRepository::class,
            \VCD2\Events\Mapper\ApplicationStepOptionMapper::class
        );
    },

    \VCD2\Events\Repository\EventRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Events\Repository\EventRepository::class,
            \VCD2\Events\Mapper\EventMapper::class
        );
    },

    \VCD2\Users\Repository\ChildRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Users\Repository\ChildRepository::class,
            \VCD2\Users\Mapper\ChildMapper::class
        );
    },

    \VCD2\Users\Repository\UserRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Users\Repository\UserRepository::class,
            \VCD2\Users\Mapper\UserMapper::class
        );
    },

    \VCD2\Users\Repository\UserRoleRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Users\Repository\UserRoleRepository::class,
            \VCD2\Users\Mapper\UserRoleMapper::class
        );
    },

    \VCD2\Emails\Repository\EmailRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Emails\Repository\EmailRepository::class,
            \VCD2\Emails\Mapper\EmailMapper::class
        );
    },

    \VCD2\Emails\Repository\AttachmentRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Emails\Repository\AttachmentRepository::class,
            \VCD2\Emails\Mapper\AttachmentMapper::class
        );
    },

    \VCD2\Events\Repository\EventTabRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Events\Repository\EventTabRepository::class,
            \VCD2\Events\Mapper\EventTabMapper::class
        );
    },

    \VCD2\Events\Repository\EventAddonRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Events\Repository\EventAddonRepository::class,
            \VCD2\Events\Mapper\EventAddonMapper::class
        );
    },

    \VCD2\Events\Repository\EventImageRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Events\Repository\EventImageRepository::class,
            \VCD2\Events\Mapper\EventImageMapper::class
        );
    },

    \VCD2\Applications\Repository\PaymentRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Applications\Repository\PaymentRepository::class,
            \VCD2\Applications\Mapper\PaymentMapper::class
        );
    },

    \Hafo\GoPay\Repository\PaymentRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \Hafo\GoPay\Repository\PaymentRepository::class,
            \Hafo\GoPay\Mapper\PaymentMapper::class
        );
    },

    \VCD2\Applications\Repository\PaymentMethodRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Applications\Repository\PaymentMethodRepository::class,
            \VCD2\Applications\Mapper\PaymentMethodMapper::class
        );
    },

    \Hafo\Fio\Repository\PaymentRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \Hafo\Fio\Repository\PaymentRepository::class,
            \Hafo\Fio\Mapper\PaymentMapper::class
        );
    },

    \VCD2\Applications\Repository\InvoiceRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Applications\Repository\InvoiceRepository::class,
            \VCD2\Applications\Mapper\InvoiceMapper::class
        );
    },

    \VCD2\Applications\Repository\InvoiceItemRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Applications\Repository\InvoiceItemRepository::class,
            \VCD2\Applications\Mapper\InvoiceItemMapper::class
        );
    },

    \VCD2\Ebooks\Repository\EbookRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Ebooks\Repository\EbookRepository::class,
            \VCD2\Ebooks\Mapper\EbookMapper::class
        );
    },

    \VCD2\Ebooks\Repository\EbookDownloadRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Ebooks\Repository\EbookDownloadRepository::class,
            \VCD2\Ebooks\Mapper\EbookDownloadMapper::class
        );
    },

    \VCD2\Ebooks\Repository\EbookDownloadLinkRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Ebooks\Repository\EbookDownloadLinkRepository::class,
            \VCD2\Ebooks\Mapper\EbookDownloadLinkMapper::class
        );
    },

    \VCD2\Carousel\Repository\CarouselRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Carousel\Repository\CarouselRepository::class,
            \VCD2\Carousel\Mapper\CarouselMapper::class
        );
    },

    \VCD2\Carousel\Repository\CarouselPageRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Carousel\Repository\CarouselPageRepository::class,
            \VCD2\Carousel\Mapper\CarouselPageMapper::class
        );
    },

    \VCD2\Gallery\Repository\PhotoRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Gallery\Repository\PhotoRepository::class,
            \VCD2\Gallery\Mapper\PhotoMapper::class
        );
    },

    \VCD2\Users\Repository\ConsentRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Users\Repository\ConsentRepository::class,
            \VCD2\Users\Mapper\ConsentMapper::class
        );
    },

    \VCD2\Users\Repository\UserSessionRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Users\Repository\UserSessionRepository::class,
            \VCD2\Users\Mapper\UserSessionMapper::class
        );
    },

    \VCD2\PostOffice\Repository\LetterRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\PostOffice\Repository\LetterRepository::class,
            \VCD2\PostOffice\Mapper\LetterMapper::class
        );
    },

    \VCD2\Reviews\Repository\ReviewRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Reviews\Repository\ReviewRepository::class,
            \VCD2\Reviews\Mapper\ReviewMapper::class
        );
    },

    \VCD2\FacebookImages\Repository\FacebookImageRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\FacebookImages\Repository\FacebookImageRepository::class,
            \VCD2\FacebookImages\Mapper\FacebookImageMapper::class
        );
    },

    \VCD2\Firebase\Repository\FirebasePushTokenRepository::class => function (Container $c) {
        return $c->create('orm.repository',
            \VCD2\Firebase\Repository\FirebasePushTokenRepository::class,
            \VCD2\Firebase\Mapper\FirebasePushTokenMapper::class
        );
    },

];
