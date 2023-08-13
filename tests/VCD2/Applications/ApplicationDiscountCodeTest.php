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
use VCD2\Discounts\DiscountCode;
use VCD2\Discounts\DiscountCodeException;
use VCD2\Discounts\DiscountCodeExpiredException;
use VCD2\Discounts\DiscountCodeRejectedException;
use VCD2\Events\Event;
use VCD2\Orm;
use VCD2\Users\User;

class ApplicationDiscountCodeTest extends TestCase
{

    const EVENT_PRICE = 3999;
    const EVENT_DEPOSIT = 500;
    const EVENT_SIBLING_DISCOUNT = 100;

    const DISCOUNT = 50;

    /** @var Orm */
    private $orm;

    public function setUp()
    {
        $this->orm = OrmLoader::getOrm();
    }

    public function testAddUnlimitedDiscountCode()
    {
        $application = $this->createTestApplication();
        $application->injectContainer($this->createTestContainer());
        self::assertEquals(self::EVENT_PRICE, $application->price, 'Price is base');

        $discountCode = new DiscountCode('test', self::DISCOUNT);
        self::assertTrue($discountCode->isUsable, 'Discount code is usable');
        $application->applyDiscountCode($discountCode);

        self::assertEquals(self::EVENT_PRICE - self::DISCOUNT, $application->price, 'Price is discounted');

        $this->orm->persistAndFlush($application);

        $application->markApplied();
        self::assertNotNull($application->appliedAt, 'Successfully applied');
    }

    public function testAddDiscountCodeWithNoUsagesLeft()
    {
        $application = $this->createTestApplication();
        $application->injectContainer($this->createTestContainer());
        self::assertEquals(self::EVENT_PRICE, $application->price, 'Price is base');

        $discountCode = new DiscountCode('test', self::DISCOUNT, 0);
        self::assertFalse($discountCode->isUsable, 'Discount code is not usable');

        $application->applyDiscountCode($discountCode); // todo check happening when applying
        $this->orm->persistAndFlush($application);

        $this->expectException(DiscountCodeException::class);
        $application->markApplied();
    }

    public function testAddDiscountCodeWithDifferentUserRestriction()
    {
        $application = $this->createTestApplication();
        $application->injectContainer($this->createTestContainer());
        self::assertEquals(self::EVENT_PRICE, $application->price, 'Price is base');
        self::assertTrue($application->canUseDiscountCode, 'Can use discount code');

        $differentUser = new User('abc@def.cz', 'Abc Def');
        $differentUser->injectContainer($this->createTestContainer());

        $discountCode = new DiscountCode('test', self::DISCOUNT);
        $discountCode->forUser = $differentUser;
        self::assertTrue($discountCode->isUsable, 'Discount code is usable');
        $this->orm->persist($discountCode);

        $application->applyDiscountCode($discountCode); // todo check happening when applying
        $this->orm->persistAndFlush($application);

        $this->expectException(DiscountCodeRejectedException::class);
        $application->markApplied();
    }

    public function testAddDiscountCodeWithCorrectUserRestriction()
    {
        $application = $this->createTestApplication();
        $application->injectContainer($this->createTestContainer());
        self::assertEquals(self::EVENT_PRICE, $application->price, 'Price is base');

        $discountCode = new DiscountCode('test', self::DISCOUNT);
        $discountCode->forUser = $application->user;
        self::assertTrue($discountCode->isUsable, 'Discount code is usable');

        $application->applyDiscountCode($discountCode);
        $this->orm->persistAndFlush($application);

        /*self::assertTrue($application->isDraft, 'Is draft');
        self::assertTrue($application->hasValidParentInfo, 'Has valid parent info');
        self::assertTrue($application->hasValidChildren, 'Has valid children');
        self::assertTrue(empty($application->unfilledSteps), 'No unfilled steps');
        self::assertTrue(empty($application->invalidStepChoices), 'No invalid step choices');
        self::assertTrue($application->event->hasOpenApplications, 'Event has open applications');
        self::assertTrue($application->event->hasEnoughCapacityFor($application->children->count()), 'Enough capacity');
        self::assertTrue($application->hasValidPaymentMethod, 'Has valid payment method');*/

        $application->markApplied();

        self::assertNotNull($application->appliedAt, 'Successfully applied');
    }

    public function testAddExpiredDiscountCode()
    {
        $application = $this->createTestApplication();
        $application->injectContainer($this->createTestContainer());
        self::assertEquals(self::EVENT_PRICE, $application->price, 'Price is base');

        $discountCode = new DiscountCode('test', self::DISCOUNT);
        $discountCode->expires = new \DateTimeImmutable('2010-01-01 00:00:00');
        self::assertTrue($discountCode->hasExpired, 'Discount code has expired');
        self::assertFalse($discountCode->isUsable, 'Discount code is not usable');

        $application->applyDiscountCode($discountCode);
        $this->orm->persistAndFlush($application);

        $this->expectException(DiscountCodeExpiredException::class);
        $application->markApplied();
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
        $user->injectContainer($this->createTestContainer());
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

        $this->orm->events->attach($event);

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
