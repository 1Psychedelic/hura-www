<?php

namespace Tests\VCD2\Applications;

use PHPUnit\Framework\TestCase;
use Tests\Fixture\EntityFactory\ApplicationChildFactory;
use Tests\Fixture\EntityFactory\ApplicationFactory;
use Tests\Fixture\EntityFactory\EventFactory;
use Tests\Fixture\EntityFactory\UserFactory;
use Tests\Fixture\OrmLoader;
use VCD2\Applications\AgeOutOfRangeException;
use VCD2\Orm;

class ApplicationBasicTest extends TestCase
{

    const EVENT_PRICE = 3999;
    const EVENT_DEPOSIT = 500;
    const EVENT_SIBLING_DISCOUNT = 100;

    /** @var Orm */
    private $orm;

    public function setUp()
    {
        $this->orm = OrmLoader::getOrm();
    }

    public function testBlankApplicationParameters()
    {
        $application = $this->createTestApplication();

        // Check basic stuff
        self::assertEquals(20, strlen($application->hash), 'Hash created');
        self::assertNotNull($application->user, 'User is set');
        self::assertEquals($application->email, $application->user->email, 'E-mail is same as user');
        self::assertFalse($application->isVip, 'No VIP');

        // Check statuses
        self::assertTrue($application->isDraft, 'Is draft');
        self::assertFalse($application->isAccepted, 'Is not accepted');
        self::assertFalse($application->isNew, 'Is not new');
        self::assertFalse($application->isRejected, 'Is not rejected');
        self::assertFalse($application->isCanceled, 'Is not canceled');
        self::assertTrue($application->isPaid, 'Price is zero, so it is paid');
        self::assertTrue($application->isDepositPaid, 'Price is zero, so it is paid');
        self::assertFalse($application->isEligibleForInvoice, 'Is not eligible for invoice');

        // Check status updates
        self::assertFalse($application->canBeApplied, 'Cannot be applied');
        self::assertFalse($application->canBeAccepted, 'Cannot be accepted');
        self::assertFalse($application->canBeRejected, 'Cannot be rejected');
        self::assertFalse($application->canBePaidFor, 'Cannot be paid for');
        self::assertFalse($application->canUseDiscountCode, 'Discount codes are not allowed atm (price is zero)');
        self::assertTrue($application->canUseSiblingDiscount, 'Sibling discount is allowed');
        self::assertFalse($application->canPayByCredit, 'Credit payment is not allowed atm (price is zero)');

        // Check filled info
        self::assertFalse($application->hasValidParentInfo, 'Doesn\'t have valid parent info');
        self::assertFalse($application->hasValidChildren, 'Doesn\'t have valid children info');
        self::assertTrue($application->hasValidPaymentMethod, 'Payment method not required when price is zero');

        // Check price
        self::assertEquals(0, $application->price, 'Price is zero (no children added)');
        self::assertEquals(0, $application->deposit, 'Deposit is zero (no children added)');
        self::assertNull($application->discount, 'No discount set');
        self::assertNull($application->discountCode, 'No discount code set');
        self::assertFalse($application->isBasePriceOverwritten, 'Base price not overwritten');
        self::assertEquals(self::EVENT_PRICE, $application->baseEventPrice, 'Base event price is OK');
        self::assertEquals(0, $application->priceWithoutCredit, 'Price is zero (no children added)');

        // Feedback
        self::assertNull($application->feedback, 'No feedback');
        self::assertNull($application->feedbackScore, 'No feedback score');

        // Payments and invoices
        self::assertEquals(0, $application->paid, 'Nothing paid');
        self::assertNull($application->creditPayment, 'No credit payment');
        self::assertEquals(0, $application->paidByCredit, 'Nothing paid by credit');
        self::assertTrue($application->isFullyPaidByCredit, 'Kinda fully paid by credit'); // todo not sure if ok

        // Invoice payment
        self::assertTrue($application->canBePaidOnInvoice, 'Can be paid on invoice');
        self::assertFalse($application->isPayingOnInvoice, 'Not paying on invoice');
    }

    public function testAgeRestriction()
    {
        $application = $this->createTestApplication();

        $this->expectException(AgeOutOfRangeException::class);

        ApplicationChildFactory::createApplicationChild($application, new \DateTimeImmutable('-3 years'));
    }

    public function testAddChildrenToApplication()
    {
        $application = $this->createTestApplication();

        $child = ApplicationChildFactory::createApplicationChild($application, new \DateTimeImmutable('-5 years'));

        self::assertTrue($application->children->has($child), 'Child was added to application');

        // Check statuses
        self::assertFalse($application->isPaid, 'Not paid');
        self::assertFalse($application->isDepositPaid, 'Deposit not paid');
        self::assertFalse($application->isEligibleForInvoice, 'Is not eligible for invoice');

        // Check status updates
        self::assertFalse($application->canBeApplied, 'Cannot be applied');
        self::assertFalse($application->canBeAccepted, 'Cannot be accepted');
        self::assertFalse($application->canBeRejected, 'Cannot be rejected');
        self::assertFalse($application->canBePaidFor, 'Cannot be paid for');
        self::assertTrue($application->canUseDiscountCode, 'Discount codes are allowed');
        self::assertTrue($application->canUseSiblingDiscount, 'Sibling discount is allowed');
        self::assertTrue($application->canPayByCredit, 'Credit payment is allowed');

        // Check filled info
        self::assertFalse($application->hasValidParentInfo, 'Doesn\'t have valid parent info');
        self::assertTrue($application->hasValidChildren, 'Does have valid children info');
        self::assertFalse($application->hasValidPaymentMethod, 'Payment method not set');

        // Check price
        self::assertEquals(self::EVENT_PRICE, $application->price, 'Price is set');
        self::assertEquals(self::EVENT_DEPOSIT, $application->deposit, 'Deposit is set');
        self::assertEquals(self::EVENT_PRICE, $application->priceWithoutCredit, 'Price without credit equals price');

        // Payments and invoices
        self::assertEquals(0, $application->paid, 'Nothing paid');
        self::assertNull($application->creditPayment, 'No credit payment');
        self::assertEquals(0, $application->paidByCredit, 'Nothing paid by credit');
        self::assertFalse($application->isFullyPaidByCredit, 'Not fully paid by credit');

        // Invoice payment
        self::assertTrue($application->canBePaidOnInvoice, 'Can be paid on invoice');
        self::assertFalse($application->isPayingOnInvoice, 'Not paying on invoice');

        $anotherChild = ApplicationChildFactory::createApplicationChild($application, new \DateTimeImmutable('-6 years'));

        self::assertTrue($application->children->has($anotherChild), 'Another child is added');

        // Check price
        $priceForTwoChildren = (self::EVENT_PRICE * 2) - (self::EVENT_SIBLING_DISCOUNT * 2);
        self::assertEquals($priceForTwoChildren, $application->price, 'Price × 2 - sibling discount × 2');
        self::assertEquals(self::EVENT_DEPOSIT * 2, $application->deposit, 'Deposit × 2');
        self::assertEquals($priceForTwoChildren, $application->priceWithoutCredit, 'Price without credit equals price');
    }

    private function createTestApplication()
    {
        return ApplicationFactory::createApplication($this->createTestEvent(), $this->createTestUser());
    }

    private function createTestUser()
    {
        return UserFactory::createUser();
    }

    private function createTestEvent()
    {
        return EventFactory::createEvent(self::EVENT_PRICE, self::EVENT_DEPOSIT, self::EVENT_SIBLING_DISCOUNT);
    }

}
