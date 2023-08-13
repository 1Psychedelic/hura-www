<?php

namespace Tests\VCD2\Applications;

use Hafo\DI\Container;
use Hafo\DI\Container\DefaultContainer;
use Hafo\Persona\Gender;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Tests\Fixture\OrmLoader;
use VCD2\Applications\Application;
use VCD2\Applications\Child;
use VCD2\Applications\PaymentMethod;
use VCD2\Discounts\Discount;
use VCD2\Events\Event;
use VCD2\Orm;
use VCD2\Users\User;

class ApplicationDiscountTest extends TestCase
{

    const EVENT_PRICE = 3999;
    const EVENT_PRICE_DISCOUNTED = 2999;
    const EVENT_DEPOSIT = 500;
    const EVENT_SIBLING_DISCOUNT = 100;

    /** @var Orm */
    private $orm;

    public function setUp()
    {
        $this->orm = OrmLoader::getOrm();
    }

    public function testDiscountInApplication()
    {
        $application = $this->createTestApplication();

        self::assertNotNull($application->event->currentDiscount, 'Event has current discount');

        self::assertTrue($application->event->isDiscounted, 'Event is discounted');

        self::assertEquals(self::EVENT_PRICE_DISCOUNTED, $application->price, 'Price is discounted');
    }

    private function createTestApplication()
    {
        $application = new Application(
            $this->createTestEvent(),
            $this->createTestUser()
        );

        $application->injectContainer($this->createTestContainer());

        $application->updateParentInfo(
            $application->user->name,
            $application->user->email,
            '123456789',
            'Brno',
            'Někde',
            '12345'
        );
        $application->markAgreement();
        $application->paymentMethod = $this->createTestPaymentMethod();
        $application->payOnlyDeposit = false;

        $this->orm->applications->attach($application);

        $child = new Child(
            $application,
            null,
            'Dítě Dítě',
            Gender::MALE,
            new \DateTimeImmutable('2045-01-01 12:00:00'),
            true,
            false,
            'Zdravé',
            'Žádné alergie',
            'Bez komentáře'
        );
        $this->orm->applicationChildren->attach($child);

        return $application;
    }

    private function createTestUser()
    {
        $user = new User('lukas@volnycasdeti.cz', 'Lukáš Klika');
        $this->orm->users->attach($user);

        return $user;
    }

    private function createTestEvent()
    {
        $event = new Event(
            Event::TYPE_TRIP,
            'Test event',
            new \DateTimeImmutable('2050-01-01 15:00:00'),
            new \DateTimeImmutable('2050-01-15 11:30:00'),
            3,      // maxParticipants
            3,      // maxReserves
            5,      // ageMin
            10,     // ageMax
            self::EVENT_PRICE,
            self::EVENT_DEPOSIT,
            self::EVENT_SIBLING_DISCOUNT,
            14      // ageCap
        );
        $event->applicableUntil = new \DateTimeImmutable('2049-12-31 00:00:00');

        $this->orm->events->persist($event);

        $discount = new Discount(
            $event,
            new \DateTimeImmutable('2000-01-01 00:00:00'),
            new \DateTimeImmutable('2025-05-01 00:00:00'),
            self::EVENT_PRICE_DISCOUNTED,
            self::EVENT_PRICE_DISCOUNTED,
            true
        );
        $this->orm->discounts->persistAndFlush($discount);

        self::assertTrue($event->discounts->has($discount), 'Discount is set');

        return $event;
    }

    private function createTestPaymentMethod()
    {
        $paymentMethod = new PaymentMethod();
        $paymentMethod->name = 'Bankovní převod';
        $paymentMethod->iconUrl = '';
        $paymentMethod->position = 0;
        $paymentMethod->isGopay = false;
        $paymentMethod->isEnabled = true;
        $this->orm->paymentMethods->persist($paymentMethod);

        return $paymentMethod;
    }

    private function createTestContainer()
    {
        return new DefaultContainer([
            Logger::class => function (Container $c) {
                $loggerMock = $this->createMock(Logger::class);
                $loggerMock->expects(self::any())
                    ->method('withName')
                    ->willReturn($loggerMock);
                return $loggerMock;
            }
        ]);
    }

}
