<?php

namespace VCD2\Applications;

use Hafo\GoPay\Payment as GoPayPayment;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasOne;
use VCD2\Applications\DTO\InvoiceItemDTO;
use VCD2\Applications\EntityTrait\Application\InvoiceInfo;
use VCD2\Applications\EntityTrait\Application\ParentInfo;
use VCD2\Applications\Repository\ApplicationRepository;
use VCD2\Discounts\Discount;
use VCD2\Discounts\DiscountException;
use VCD2\Entity;
use Nette\Utils\Random;
use Nette\Utils\Validators;
use Nextras\Orm\Relationships\OneHasMany;
use VCD2\Credits\CreditMovement;
use VCD2\Discounts\DiscountCode;
use VCD2\Discounts\DiscountCodeException;
use VCD2\Events\ApplicationStep;
use VCD2\Events\Event;
use VCD2\Users\User;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property string|NULL $hash
 * @property Event $event {m:1 Event::$applications}
 * @property User|NULL $user {m:1 User::$applications}
 * @property OneHasMany|Child[] $children {1:m Child::$application, cascade=[persist, remove]}
 * @property string|null $notes {default NULL}
 * @property string|null $internalNotes {default NULL}
 *
 **** IP/Host
 * @property string $ip {default ''}
 * @property string $host {default ''}
 *
 *
 **** Udělené souhlasy
 * @property bool $agreedPersonalData {default FALSE}
 * @property bool $agreedTermsAndConditions {default FALSE}
 * @property bool $agreedPhotography {default FALSE}
 * @property bool $saveProfile {default FALSE}
 *
 *
 **** Platební metoda
 * @property bool|NULL $payOnlyDeposit
 * @property ManyHasOne|PaymentMethod|NULL $paymentMethod {m:1 PaymentMethod, oneSided=TRUE}
 * @property-read GoPayPayment|NULL $createdGoPayPayment {virtual}
 *
 *
 **** Příznaky
 * @property bool $isReserve {default FALSE}
 * @property bool $isVip {virtual}
 * @property int $vipLevel {default 0}
 * @property bool $isPayingByCredit {default FALSE}
 *
 * @property-read string $status {virtual}
 *
 * @property bool $isApplied {default FALSE}
 * @property bool $isAccepted {default FALSE}
 * @property-read bool $isDraft {virtual}
 * @property-read bool $isNew {virtual}
 * @property bool $isRejected {default FALSE}
 * @property bool $isCanceled {default FALSE}
 * @property-read bool $isPaid {virtual}
 * @property-read bool $isDepositPaid {virtual}
 * @property-read bool $isEligibleForInvoice {virtual}
 * @property bool $hasInvoice {default FALSE}
 *
 * @property-read bool $canBeApplied {virtual}
 * @property-read bool $canBeAccepted {virtual}
 * @property-read bool $canBeRejected {virtual}
 * @property-read bool $canBePaidFor {virtual}
 * @property-read bool $canUseDiscountCode {virtual}
 * @property-read bool $canUseSiblingDiscount {virtual}
 * @property-read bool $canPayByCredit {virtual}
 *
 * @property-read bool $hasValidParentInfo {virtual}
 * @property-read bool $hasValidChildren {virtual}
 * @property-read bool $hasValidPaymentMethod {virtual}
 * @property-read bool $hasFilledPayingOnInvoice {virtual}
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable $createdAt {default now}
 * @property \DateTimeImmutable|NULL $updatedAt {default now}
 * @property \DateTimeImmutable|NULL $appliedAt {default NULL}
 * @property \DateTimeImmutable|NULL $acceptedAt {default NULL}
 * @property \DateTimeImmutable|NULL $rejectedAt {default NULL}
 * @property \DateTimeImmutable|NULL $canceledAt {default NULL}
 * @property \DateTimeImmutable|NULL $paidAt {default NULL}
 *
 *
 **** Cena a slevy
 * @property int $price
 * @property int $deposit
 * @property DiscountCode|NULL $discountCode {m:1 DiscountCode::$applications}
 * @property ManyHasOne|Discount|NULL $discount {m:1 Discount::$applications}
 * @property-read bool $isBasePriceOverwritten {virtual}
 * @property-read int $baseEventPrice {virtual}
 * @property-read int $priceWithoutCredit {virtual}
 *
 *
 **** Feedback
 * @property int|NULL $feedbackScore {default NULL}
 * @property string|NULL $feedback {default NULL}
 *
 *
 **** Platby a faktura
 * @property-read int $paid {virtual}
 * @property OneHasMany|CreditMovement[] $creditMovements {1:m CreditMovement::$application}
 * @property OneHasMany|Payment[] $payments {1:m Payment::$application}
 * @property OneHasOne|Invoice|NULL $invoice {1:1 Invoice::$application}
 * @property-read CreditMovement|NULL $creditPayment {virtual}
 * @property-read int $paidByCredit {virtual}
 * @property-read bool $isFullyPaidByCredit {virtual}
 *
 *
 **** Úhrada zaměstnavatelem
 * @property-read bool $canBePaidOnInvoice {virtual}
 * @property bool $isPayingOnInvoice {default FALSE}
 * @property-read bool $hasValidInvoiceInfo {virtual}
 *
 **** Mezikroky
 * @property OneHasMany|ApplicationAddon[] $addons {1:m ApplicationAddon::$application, orderBy=[this->addon->position=ASC]}
 * @property OneHasMany|StepChoice[] $stepChoices {1:m StepChoice::$application, cascade=[persist, remove], orderBy=[this->option->absolutePrice=DESC]}
 * @property-read ApplicationStep[] $unfilledSteps {virtual}
 * @property-read StepChoice[] $invalidStepChoices {virtual}
 *
 *
 **** Deprecated
 * @property int $appliedChildren {default 0}
 *
 *
 * @see ApplicationRepository
 */
class Application extends Entity
{
    use InvoiceInfo;
    use ParentInfo;

    public const STATUS_DRAFT = 'STATUS_DRAFT';
    public const STATUS_NEW = 'STATUS_NEW';
    public const STATUS_ACCEPTED = 'STATUS_ACCEPTED';
    public const STATUS_CANCELED = 'STATUS_CANCELED';
    public const STATUS_REJECTED = 'STATUS_REJECTED';

    public function __construct(Event $event, User $user = null)
    {
        parent::__construct();

        $this->event = $event;
        $this->user = $user;

        $this->price = $event->price;
        $this->deposit = $event->deposit;

        $this->generateHash();

        if ($user !== null) {
            $this->ip = $user->ip;
            $this->host = $user->host;

            $this->updateParentInfo($user->name, $user->email, $user->phone, $user->city, $user->street, $user->zip);

            if ($user->isPayingOnInvoice && $this->event->price > 0) {
                $this->setPayingOnInvoice(
                    $user->invoiceName,
                    $user->invoiceIco,
                    $user->invoiceDic,
                    $user->invoiceCity,
                    $user->invoiceStreet,
                    $user->invoiceZip,
                    $user->invoiceNotes
                );
            }

            $this->vipLevel = $user->vipLevel;

            $this->payOnlyDeposit = $user->payOnlyDeposit;
            $this->paymentMethod = $user->paymentMethod;
        }

        $this->refreshDiscount();
    }

    public function onBeforeUpdate()
    {
        parent::onBeforeUpdate();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function onBeforePersist()
    {
        parent::onBeforePersist();
        if ($this->discountCode !== null) {
            $this->discountCode->recalculateUsagesLeft();
        }

        $this->updatePaidAt();

        if ($this->user !== null) {
            $this->user->updateVipStatus();
        }
    }

    public function recalculatePrice()
    {
        $this->calculateDeposit();
        $this->calculatePrice();
    }

    public function applyDiscountCode(DiscountCode $discountCode)
    {
        $this->discountCode = $discountCode;
        $this->recalculatePrice();
    }

    public function resetDiscountCode()
    {
        $this->discountCode = null;
        $this->recalculatePrice();
    }

    public function refreshDiscount()
    {
        $currentPriceLevel = $this->event->currentPriceLevel;

        $discount = null;
        if ($currentPriceLevel->isPersisted()) {
            $discount = $this->event->currentPriceLevel;
        }

        if ($this->event->currentDiscount !== null) {
            if ($this->isVip) {
                $discountHasVip = $this->event->currentDiscount->priceVip > 0;
                $levelHasVip = $currentPriceLevel->priceVip > 0;
                if ($discountHasVip && $levelHasVip && $this->event->currentDiscount->priceVip < $currentPriceLevel->priceVip) {
                    $discount = $this->event->currentDiscount;
                }
            } else {
                $discount = $this->event->currentDiscount;
            }
        }

        $this->discount = $discount;
        $this->recalculatePrice();
    }

    /**
     * @throws ApplicationException
     * @throws DiscountCodeException
     * @throws DiscountException
     */
    public function markApplied()
    {
        $this->assertCanBeApplied();

        // set reserves flag
        if ($this->event->wouldBeReserves($this->children->count())) {
            $this->isReserve = true;
        }

        // validate discount code
        if ($this->discountCode !== null) {
            try {
                $this->discountCode->checkRequirementsForApplication($this);
            } catch (DiscountCodeException $e) {
                $this->discountCode = null;
                $this->recalculatePrice();

                throw $e;
            }
        }

        // validate discount
        if ($this->discount !== null) {
            try {
                $this->discount->checkRequirementsForApplication($this, new \DateTimeImmutable());
            } catch (DiscountException $e) {
                $this->discount = null;
                $this->recalculatePrice();

                throw $e;
            }
        }

        // mark as applied
        $this->appliedAt = new \DateTimeImmutable();
        $this->isApplied = true;

        // consume discount
        if ($this->discountCode !== null) {
            $this->discountCode->recalculateUsagesLeft();
        }
    }

    public function markAccepted()
    {
        if (!$this->canBeAccepted) {
            throw ApplicationException::create(
                sprintf('Application %s cannot be marked as accepted.', $this->id),
                sprintf('Přihlášku %s se nepodařilo označit jako přijatou.', $this->id)
            );
        }
        $this->acceptedAt = new \DateTimeImmutable();
        $this->isAccepted = true;
        $this->rejectedAt = null;
        $this->isRejected = false;
    }

    public function markRejected()
    {
        if (!$this->canBeRejected) {
            throw ApplicationException::create(
                sprintf('Application %s cannot be marked as rejected.', $this->id),
                sprintf('Přihlášku %s se nepodařilo označit jako odmítnutou.', $this->id)
            );
        }
        $this->rejectedAt = new \DateTimeImmutable();
        $this->isRejected = true;
        $this->acceptedAt = null;
        $this->isAccepted = false;
    }

    public function updateParentInfo($name, $email, $phone, $city, $street, $zip)
    {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->city = $city;
        $this->street = $street;
        $this->zip = $zip;
    }

    public function markAgreement($agreedPersonalData = true, $agreedTermsAndConditions = true, $agreedPhotography = true)
    {
        $this->agreedPersonalData = $agreedPersonalData;
        $this->agreedTermsAndConditions = $agreedTermsAndConditions;
        $this->agreedPhotography = $agreedPhotography;
    }

    public function setPayingOnInvoice($name, $ico, $dic, $city, $street, $zip, $notes)
    {
        $this->isPayingOnInvoice = true;

        $this->invoiceName = $name;
        $this->invoiceIco = $ico;
        $this->invoiceDic = $dic;
        $this->invoiceCity = $city;
        $this->invoiceStreet = $street;
        $this->invoiceZip = $zip;
        $this->invoiceNotes = $notes;
    }

    public function resetPayingOnInvoice()
    {
        $this->isPayingOnInvoice = false;

        $this->invoiceName = null;
        $this->invoiceIco = null;
        $this->invoiceDic = null;
        $this->invoiceCity = null;
        $this->invoiceStreet = null;
        $this->invoiceZip = null;
        $this->invoiceNotes = null;
    }

    public function updatePaidAt()
    {
        if (!$this->isPaid) {
            $this->paidAt = null;

            return;
        }

        $payment = $this->payments->get()->orderBy('createdAt', ICollection::DESC)->fetch();
        if ($payment instanceof Payment) {
            $this->paidAt = $payment->createdAt;
        } else {
            $this->paidAt = $this->appliedAt; // nejspíš platba kreditem
        }
    }

    protected function getterCanBePaidOnInvoice()
    {
        if ($this->children->count() > 0) {
            return $this->price > 0;
        }

        return $this->baseEventPrice > 0;
    }

    protected function getterCreatedGoPayPayment()
    {
        /** @var Payment $payment */
        $payment = $this->payments->get()->resetOrderBy()->orderBy('createdAt', ICollection::DESC)->getBy([
            'gopayPayment!=' => null,
            'this->gopayPayment->state' => GoPayPayment::STATES_INIT,
        ]);
        if ($payment !== null && $payment->gopayPayment !== null) {
            return $payment->gopayPayment;
        }

        return null;
    }

    private function generateHash()
    {
        $this->hash = Random::generate(20);
    }

    protected function getterStatus()
    {
        if ($this->isDraft) {
            return self::STATUS_DRAFT;
        }
        if ($this->isNew) {
            return self::STATUS_NEW;
        }
        if ($this->isAccepted) {
            return self::STATUS_ACCEPTED;
        }
        if ($this->isCanceled) {
            return self::STATUS_CANCELED;
        }
        if ($this->isRejected) {
            return self::STATUS_REJECTED;
        }

        return self::STATUS_DRAFT;
    }

    protected function getterIsAccepted()
    {
        return $this->appliedAt !== null && $this->acceptedAt !== null && $this->canceledAt === null && $this->rejectedAt === null;
    }

    protected function getterIsDraft()
    {
        return $this->appliedAt === null && $this->acceptedAt === null && $this->canceledAt === null && $this->rejectedAt === null;
    }

    protected function getterIsNew()
    {
        return $this->appliedAt !== null && $this->acceptedAt === null && $this->canceledAt === null && $this->rejectedAt === null;
    }

    protected function getterIsRejected()
    {
        return $this->rejectedAt !== null;
    }

    protected function getterIsCanceled()
    {
        return $this->canceledAt !== null;
    }

    protected function getterIsPaid()
    {
        return $this->paid >= $this->price;
    }

    protected function getterIsDepositPaid()
    {
        return $this->paid >= $this->deposit;
    }

    protected function getterIsEligibleForInvoice()
    {
        return $this->isAccepted
            && $this->isPaid
            && !$this->isFullyPaidByCredit
            && $this->price > 0;
    }

    protected function getterHasValidInvoiceInfo()
    {
        return $this->invoiceName
            && $this->invoiceIco
            && $this->invoiceDic
            && $this->invoiceStreet
            && $this->invoiceCity
            && $this->invoiceZip;
    }

    public function assertCanBeApplied() {
        if (!$this->isDraft) {
            throw ApplicationException::create(
                sprintf('Application %s cannot be marked as applied, because it is not a draft.', $this->id),
                'Přihláška již byla odeslaná, zkontrolujte svůj uživatelský profil.'
            );
        }

        if (!$this->hasValidParentInfo) {
            throw InvalidParentInfoException::create(
                sprintf('Application %s cannot be marked as applied, because it does not contain valid parent info.', $this->id),
                'Přihlášku se nepodařilo odeslat, zkontrolujte prosím, zda jsou správně vyplněné všechny informace o zákonném zástupci.'
            );
        }

        if (!$this->hasValidChildren) {
            throw InvalidChildrenException::create(
                sprintf('Application %s cannot be marked as applied, because it does not contain valid children.', $this->id),
                'Přihlášku se nepodařilo odeslat, zkontrolujte prosím, zda jsou správně vyplněné všechny informace o dětech.'
            );
        }

        if (!$this->event->hasOpenApplications) {
            throw ApplicationClosedException::create(
                sprintf('Application %s cannot be marked as applied, because applications for this event are closed.', $this->id),
                'Přihlášku se nepodařilo odeslat, protože přihlášky na tuto akci již byly uzavřeny.'
            );
        }

        if (!$this->event->hasEnoughCapacityFor($this->children->count())) {
            throw ApplicationCapacityException::create(
                sprintf('Application %s cannot be marked as applied, because of insufficient capacity.', $this->id),
                'Přihlášku se nepodařilo odeslat, bohužel byl někdo před vámi rychlejší a už nemáme kapacitu.'
            );
        }

        /*if (!$this->hasValidPaymentMethod) {
            throw PaymentException::create(
                sprintf('Application %s cannot be marked as applied, because it does not contain a valid payment method.', $this->id),
                'Přihlášku se nepodařilo odeslat, protože nemáte zvolenou platební metodu.'
            );
        }*/
    }

    protected function getterCanBeApplied()
    {
        return $this->isDraft
            && $this->hasValidParentInfo
            && $this->hasValidChildren
            //&& empty($this->unfilledSteps)
            //&& empty($this->invalidStepChoices)
            && $this->event->hasOpenApplications
            && $this->event->hasEnoughCapacityFor($this->children->count());
            //&& $this->hasValidPaymentMethod;
    }

    protected function getterCanBeAccepted()
    {
        return $this->appliedAt !== null && $this->acceptedAt === null && $this->canceledAt === null;
    }

    protected function getterCanBeRejected()
    {
        return $this->appliedAt !== null && $this->rejectedAt === null && $this->canceledAt === null;
    }

    protected function getterCanBePaidFor()
    {
        return $this->appliedAt !== null && $this->rejectedAt === null && $this->canceledAt === null && !$this->isPaid;
    }

    protected function getterCanUseDiscountCode()
    {
        if ($this->price <= 0) {
            return false;
        }

        if ($this->isVip) {
            return false;
        }

        if ($this->discount !== null && !$this->discount->allowDiscountCodes) {
            return false;
        }

        foreach ($this->stepChoices as $stepChoice) {
            if (!$stepChoice->option->allowDiscountCodes) {
                return false;
            }
        }

        return true;
    }

    protected function getterCanPayByCredit()
    {
        if ($this->priceWithoutCredit <= 0) {
            return false;
        }

        if ($this->discount !== null && !$this->discount->allowCredits) {
            return false;
        }

        return true;
    }

    protected function getterCanUseSiblingDiscount()
    {
        if ($this->discount !== null && !$this->discount->allowSiblingDiscount) {
            return false;
        }

        foreach ($this->stepChoices as $stepChoice) {
            if (!$stepChoice->option->allowSiblingDiscount) {
                return false;
            }
        }

        return true;
    }

    protected function getterHasValidParentInfo()
    {
        return Validators::isEmail($this->email)
            && strlen($this->phone) > 2
            && strlen($this->city) > 2
            && strlen($this->street) > 2
            && strlen($this->zip) > 4
            && $this->agreedPersonalData
            && $this->agreedPhotography
            && $this->agreedTermsAndConditions;
    }

    protected function getterHasValidChildren()
    {
        $countChildren = $this->children->count();

        // must have at least one child
        if ($countChildren === 0) {
            return false;
        }

        // draft must fit into event's capacity
        //if($this->isDraft && !$this->event->hasEnoughCapacityFor($countChildren)) {
        //    return FALSE;
        //}

        // children must have valid info
        foreach ($this->children as $child) {
            if (!$child->hasValidInfo) {
                return false;
            }
        }

        return true;
    }

    protected function getterUnfilledSteps()
    {
        $steps = [];
        foreach ($this->event->steps as $step) {
            if ($this->stepChoices->get()->getBy(['step' => $step->id]) === null) {
                $steps[] = $step;
            }
        }

        return $steps;
    }

    protected function getterInvalidStepChoices()
    {
        $choices = [];
        foreach ($this->stepChoices as $stepChoice) {
            if ($stepChoice->option->maxUsages !== null && ($stepChoice->option->maxUsages - $stepChoice->option->timesUsed < $this->children->count())) {
                $choices[] = $stepChoice;
            }
        }

        return $choices;
    }

    protected function getterHasFilledPayingOnInvoice()
    {
        return $this->invoiceName && $this->invoiceIco && $this->invoiceDic && $this->invoiceStreet && $this->invoiceCity && $this->invoiceZip;
    }

    protected function getterHasValidPaymentMethod()
    {
        if ($this->price === null || $this->price <= 0) {
            return true;
        }
        if ($this->paymentMethod === null) {
            return false;
        }

        return $this->paymentMethod->isEnabled && $this->payOnlyDeposit !== null;
    }

    protected function getterCreditPayment()
    {
        foreach ($this->creditMovements as $movement) {
            if ($movement->difference < 0) {
                return $movement;
            }
        }

        return null;
    }

    protected function getterPaidByCredit()
    {
        $paid = 0;
        foreach ($this->creditMovements as $movement) {
            $paid -= $movement->difference;
        }

        return $paid;
    }

    protected function getterIsFullyPaidByCredit()
    {
        return $this->paidByCredit === $this->price;
    }

    protected function getterIsBasePriceOverwritten()
    {
        return $this->stepChoices->get()->getBy(['this->option->absolutePrice' => true]) !== null;
    }

    protected function getterPaid()
    {
        $paid = 0;
        foreach ($this->payments as $payment) {
            if ($payment->isPaid) {
                $paid += $payment->amount;
            }
        }

        return $paid;
    }

    /**
     * @return InvoiceItemDTO[]
     */
    public function createInvoiceItems()
    {
        $items = $this->createSummaryItems();

        // Úhrada zaměstnavatelem
        if ($this->isPayingOnInvoice) {
            /** @var InvoiceItemDTO $firstItem */
            $firstItem = array_shift($items);
            $firstItem->name = 'Fakturujeme vám za pobytovou akci pro ' . (count($this->children) > 1 ? 'děti' : 'dítě') . ' vašeho zaměstnance.' . "\r\n\r\n";
            $firstItem->name .= 'Název akce: ' . $this->event->name . "\r\n";
            $firstItem->name .= 'Zaměstnanec: ' . $this->name;
            foreach ($this->children as $child) {
                $firstItem->name .= "\r\n" . 'Dítě: ' . $child->name . ' nar. ' . $child->dateBorn->format('j.n.Y');
            }
            array_unshift($items, $firstItem);
        }

        return $items;
    }

    /**
     * @return InvoiceItemDTO[]
     */
    public function createSummaryItems()
    {
        $items = [];

        $countChildren = $this->children->countStored();

        $basePrice = $this->baseEventPrice;
        $multiplyByChildren = true;

        if ($this->isBasePriceOverwritten) {
            /** @var StepChoice $stepChoice */
            $stepChoice = $this->stepChoices->get()->getBy(['this->option->absolutePrice' => true]);
            $basePrice = $stepChoice->option->price;
            $multiplyByChildren = $stepChoice->option->multiplyByChildren;
        }

        // Účast na akci
        $items[] = $firstItem = new InvoiceItemDTO($this->event->name, $basePrice, $multiplyByChildren ? $countChildren : 1, $multiplyByChildren ? $basePrice * $countChildren : $basePrice);

        // Mezikroky
        foreach ($this->stepChoices as $stepChoice) {
            $option = $stepChoice->option;
            if ($option->absolutePrice || $option->price === 0) {
                continue;
            }
            $mul = $option->multiplyByChildren ? $countChildren : 1;
            $items[] = new InvoiceItemDTO($stepChoice->option->option, $option->price, $mul, $option->price * $mul);
        }

        // Doplňky
        foreach ($this->addons as $applicationAddon) {
            if ($applicationAddon->amount === 0) {
                continue;
            }
            $items[] = new InvoiceItemDTO($applicationAddon->addon->name, $applicationAddon->price, $applicationAddon->amount, $applicationAddon->price * $applicationAddon->amount);
        }

        // Sourozenecká sleva
        if ($this->canUseSiblingDiscount && $this->event->siblingDiscountValueFor($countChildren) > 0) {
            $items[] = new InvoiceItemDTO('Sourozenecká sleva', -$this->event->siblingDiscount, $countChildren, -$this->event->siblingDiscountValueFor($countChildren));
        }

        // Slevové kódy
        if ($this->canUseDiscountCode && $this->discountCode !== null) {
            $mul = $this->discountCode->multiplyByChildren ? $countChildren : 1;
            $items[] = new InvoiceItemDTO(sprintf('Slevový kód "%s"', $this->discountCode->code), -$this->discountCode->discount, $mul, -$this->discountCode->discountValueFor($countChildren));
        }

        // Kredity
        if ($this->paidByCredit > 0) {
            $items[] = new InvoiceItemDTO('Sleva - platba kreditem', -$this->paidByCredit, 1, -$this->paidByCredit);
        }

        return $items;
    }

    private function calculateDeposit()
    {
        $countChildren = $this->children->countStored();
        $deposit = $this->event->deposit * $countChildren;
        $this->deposit = $deposit;
    }

    private function calculatePrice()
    {
        $previousPrice = $this->price;

        // Základní cena
        $price = $this->calculatePriceWithoutCredit();

        // Platba kreditem (neodeslaná přihláška) - odečti podle stavu konta uživatele
        if ($this->appliedAt === null && $this->user !== null && $this->isPayingByCredit) {
            $price -= $this->user->creditBalance;
        }

        // Platba kreditem (odeslaná přihláška) - odečti kolik bylo zaplaceno kreditem
        if ($this->appliedAt !== null) {
            $price -= $this->paidByCredit;
        }

        // Nula spodní limit
        if ($price < 0) {
            $price = 0;
        }

        $this->price = $price;

        if ($this->isPersisted()) {
            $this->logger->info(sprintf('Přepočítávám cenu přihlášky %s, stará cena %s Kč, nová cena %s Kč', $this, $previousPrice, $price));
        }
    }

    protected function getterBaseEventPrice()
    {
        if ($this->vipLevel === 2) {
            return 0;
        }

        if ($this->isVip) {
            if ($this->discount !== null && $this->discount->priceVip > 0) {
                // VIP cena ve slevě
                return $this->discount->priceVip;
            } elseif ($this->discount !== null) {
                // Sleva nemá VIP, akce má VIP - vyber výhodnější
                return $this->event->priceVip > 0 && $this->event->priceVip < $this->discount->price
                    ? $this->event->priceVip
                    : $this->discount->price;
            }

            return $this->event->priceVip > 0 ? $this->event->priceVip : $this->event->price;
        }

        return $this->discount !== null ? $this->discount->price : $this->event->price;
    }

    protected function getterPriceWithoutCredit()
    {
        return $this->calculatePriceWithoutCredit();
    }

    protected function getterIsVip(): bool
    {
        return $this->vipLevel > 0;
    }

    private function calculatePriceWithoutCredit()
    {
        $basePrice = $this->baseEventPrice;

        $countChildren = $this->children->countStored();
        $price = $basePrice * $countChildren;
        $relative = 0;

        foreach ($this->stepChoices as $stepChoice) {
            $option = $stepChoice->option;
            if ($option->absolutePrice) {
                $price = ($option->price * $countChildren) + $relative;
            } else {
                $change = $option->price * ($option->multiplyByChildren ? $countChildren : 1);
                $relative += $change;
                $price += $change;
            }
        }

        foreach ($this->addons as $applicationAddon) {
            $price += $applicationAddon->amount * $applicationAddon->price;
        }

        if ($this->canUseSiblingDiscount) {
            $price -= $this->event->siblingDiscountValueFor($countChildren);
        }

        if ($this->canUseDiscountCode && $this->discountCode !== null) {
            $price -= $this->discountCode->discountValueFor($countChildren);
        }

        return $price;
    }
}
