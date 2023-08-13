<?php

namespace VCD2\Users;

use DateTime;
use DateTimeImmutable;
use Hafo\Facebook\FacebookPixel\FacebookPixelUserDataSource;
use Hafo\Security\Storage\Avatars;
use Nette\Utils\Strings;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use VCD2\Applications\Application;
use VCD2\Applications\PaymentMethod;
use VCD2\Credits\Credit;
use VCD2\Credits\CreditMovement;
use VCD2\Entity;
use VCD2\Events\Event;
use VCD2\Firebase\FirebasePushToken;
use VCD2\PostOffice\Letter;
use VCD2\Users\EntityTrait\User\FacebookInfo;
use VCD2\Users\EntityTrait\User\GoogleInfo;
use VCD2\Users\EntityTrait\User\InvoiceInfo;
use VCD2\Users\EntityTrait\User\UserInfo;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property ManyHasMany|Event[] $eventsForUser {m:m Event::$openForUsers}
 * @property OneHasMany|UserRole[] $roles {1:m UserRole::$user}
 *
 *
 **** Údaje o uživateli
 *
 *
 **** Úhrada zaměstnavatelem
 * @property bool $isPayingOnInvoice {default FALSE}
 *
 *
 **** Přihlašovací údaje
 * @property string|NULL $loginToken
 * @property string|NULL $loginHash
 * @property string|NULL $password
 * @property string|NULL $passwordRestore
 *
 *
 **** IP/Host
 * @property string $ip {default ''}
 * @property string $host {default ''}
 *
 *
 **** Udělené souhlasy
 * @property OneHasMany|Consent[] $consents {1:m Consent::$user}
 * @property bool $agreedPersonalData {default FALSE}
 * @property bool $agreedTermsAndConditions {default FALSE}
 * @property bool $agreedPhotography {default FALSE}
 * @property bool $agreedSms {default FALSE}
 *
 *
 **** Ověření e-mailu/telefonu
 * @property bool $emailVerified {default FALSE}
 * @property bool $phoneVerified {default FALSE}
 * @property string|NULL $emailVerifyHash
 *
 *
 **** Avatar
 * @property string|NULL $avatarSmall
 * @property string|NULL $avatarLarge
 * @property \DateTimeImmutable|NULL $avatarUpdated
 * @property int|NULL $avatarSource {enum Avatars::SOURCE_*}
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable $registeredAt {default now}
 * @property \DateTimeImmutable|NULL $lastLogin
 * @property \DateTimeImmutable|NULL $lastActive
 *
 *
 **** Facebook
 * @property string|NULL $facebookGender {enum male, female}
 * @property string|NULL $facebookGrantedPermissions
 * @property bool|NULL $facebookVerified
 * @property string|NULL $facebookWebsite
 * @property string|NULL $facebookLocale
 *
 *
 **** Google
 *
 *
 **** Platební metoda
 * @property bool|NULL $payOnlyDeposit
 * @property ManyHasOne|PaymentMethod|NULL $paymentMethod {m:1 PaymentMethod, oneSided=TRUE}
 *
 *
 **** Kredity
 * @property OneHasMany|Credit[] $credits {1:m Credit::$user, orderBy=[expiresAt=ASC]}
 * @property OneHasMany|CreditMovement[] $creditMovements {1:m CreditMovement::$user}
 * @property-read Credit|NULL $creditWithNearestExpiration {virtual}
 * @property-read CreditMovement|NULL $lastCreditMovement {virtual}
 * @property-read int $creditBalance {virtual}
 *
 *
 **** Děti
 * @property ManyHasMany|Child[] $children {m:m Child::$parents}
 *
 *
 **** Přihlášky
 * @property OneHasMany|Application[] $applications {1:m Application::$user, orderBy=[id=DESC]}
 * @property-read Application[]|ICollection $appliedApplications {virtual}
 * @property-read Application[]|ICollection $acceptedApplications {virtual}
 * @property-read Application[]|ICollection $applicationsEligibleForVip {virtual}
 *
 *
 **** Účast na akcích a diplomy
 * @property-read int $countEventsParticipated {virtual}
 * @property-read int $countDiplomas {virtual}
 *
 *
 **** Dopisy
 * @property OneHasMany|Letter[] $letters {1:m Letter::$user, orderBy=[createdAt=DESC]}
 * @property-read OneHasMany|Letter[] $visibleLetters {virtual}
 *
 *
 **** Příznaky
 * @property int $vipLevel {default 0}
 * @property bool $isVip {virtual}
 * @property-read bool $canLogin {virtual}
 * @property OneHasMany|UserSession[] $sessions {1:m UserSession::$user, orderBy=[lastSeen=DESC]}
 * @property OneHasMany|FirebasePushToken[] $firebasePushTokens {1:m FirebasePushToken::$user}
 *
 *
 **** Deprecated
 * @property bool $saveProfile {default FALSE}
 *
 *
 */
class User extends Entity implements \Hafo\User\User, FacebookPixelUserDataSource {

    use UserInfo;
    use InvoiceInfo;
    use FacebookInfo;
    use GoogleInfo;

    public const APPLICATIONS_FOR_VIP = 5; // Kolika akcí se musí zúčastnit, aby byl VIP

    function __construct($email, $name) {
        parent::__construct();
        $this->email = $email;
        $this->name = $name;
    }

    function onBeforePersist() {
        parent::onBeforePersist();

        $this->updateVipStatus();
    }

    function updateInfo($name, $phone, $city, $street, $zip) {
        $this->name = $name;
        $this->phone = $phone;
        $this->city = $city;
        $this->street = $street;
        $this->zip = $zip;
    }

    function markAgreement($agreedPersonalData = TRUE, $agreedTermsAndConditions = TRUE, $agreedPhotography = TRUE, $agreedSms = FALSE) {
        $this->agreedPersonalData = $agreedPersonalData;
        $this->agreedTermsAndConditions = $agreedTermsAndConditions;
        $this->agreedPhotography = $agreedPhotography;
        $this->agreedSms = $agreedSms;
    }

    function setPayingOnInvoice($name, $ico, $dic, $city, $street, $zip, $notes) {
        $this->isPayingOnInvoice = TRUE;

        $this->invoiceName = $name;
        $this->invoiceIco = $ico;
        $this->invoiceDic = $dic;
        $this->invoiceCity = $city;
        $this->invoiceStreet = $street;
        $this->invoiceZip = $zip;
        $this->invoiceNotes = $notes;
    }

    function resetPayingOnInvoice() {
        $this->isPayingOnInvoice = FALSE;
    }

    protected function getterCreditWithNearestExpiration() {
        /** @var Credit $credit */
        $nonExpiration = NULL;
        $now = new \DateTimeImmutable;
        foreach($this->credits->get()->orderBy('expiresAt', ICollection::ASC) as $credit) {
            if($credit->expiresAt === NULL) {
                $nonExpiration = $credit;
                continue;
            } else if($credit->expiresAt < $now || $credit->amount <= 0) {
                continue;
            }
            return $credit;
        }
        return $nonExpiration;
    }
    
    protected function getterLastCreditMovement() {
        return $this->creditMovements->get()->orderBy('id', ICollection::DESC)->limitBy(1)->fetch();
    }

    protected function getterCreditBalance() {
        $now = new \DateTimeImmutable;
        $balance = 0;
        foreach($this->credits as $credit) {
            if($credit->expiresAt === NULL || $credit->expiresAt > $now) {
                $balance += $credit->amount;
            }
        }
        return $balance;
    }

    protected function getterCountEventsParticipated() {
        $result = 0;
        foreach($this->children as $child) {
            $result += count($child->eventsParticipated);
        }
        return $result;
    }

    protected function getterCountDiplomas() {
        $result = 0;
        foreach($this->children as $child) {
            $result += count($child->diplomas);
        }
        return $result;
    }

    protected function getterAppliedApplications() {
        return $this->applications->get()->findBy(['appliedAt!=' => NULL])->resetOrderBy()->orderBy('appliedAt', ICollection::DESC);
    }

    protected function getterAcceptedApplications() {
        return $this->applications->get()->findBy([
            'appliedAt!=' => NULL,
            'acceptedAt!=' => NULL,
            'canceledAt' => NULL,
            'rejectedAt' => NULL,
        ])->resetOrderBy()->orderBy('appliedAt', ICollection::DESC);
    }

    protected function getterApplicationsEligibleForVip() {
        return $this->applications->get()->findBy([
            'isApplied' => true,
            'isAccepted' => true,
            'isCanceled' => false,
            'isRejected' => false,
            'price>' => 0,
            'paidAt!=' => null,
            'this->event->ends<' => new DateTimeImmutable(),
        ])->resetOrderBy()->orderBy('appliedAt', ICollection::DESC);
    }

    public function updateVipStatus() {
        if ($this->vipLevel > 1) {
            return;
        }

        $previousStatus = $this->vipLevel;
        $this->vipLevel = (int)($this->applicationsEligibleForVip->countStored() >= self::APPLICATIONS_FOR_VIP);

        if ($previousStatus !== $this->isVip) {
            $this->logger->notice(
                sprintf('Změněn VIP status uživatele %s z %s na %s.', (string)$this, (int)$previousStatus, (int)$this->isVip)
            );
        }
    }

    protected function getterCanLogin() {
        return ($this->password !== NULL && strlen($this->password) > 0)
        || ($this->facebookId !== NULL && strlen($this->facebookId) > 0)
        || ($this->googleId !== NULL && strlen($this->googleId) > 0);
    }

    public function isAdmin() {
        return (bool)$this->roles->get()->findBy(['role' => UserRole::ROLE_ADMIN])->fetch();
    }

    /**
     * @param int $amount
     * @param string|NULL $notes
     * @param Application|NULL $application
     * @return CreditMovement|void
     * @throws InsufficientCreditException
     * @throws \InvalidArgumentException
     */
    public function spendCredits($amount, $notes = NULL, Application $application = NULL) {
        $amount = abs($amount);
        if($amount === 0) {
            return;
        }
        if($this->creditBalance < $amount) {
            throw new InsufficientCreditException('Insufficient credit balance.');
        }
        if($application !== NULL && $application->user !== $this) {
            throw new \InvalidArgumentException(sprintf('Application %s does not belong to user %s.', $application->id, $this->id));
        }

        /**
         * @var Credit[] $withoutExpiration
         * @var Credit[] $withExpiration
         * @var Credit[] $all
         */
        $withoutExpiration = [];
        $withExpiration = [];
        foreach($this->credits as $credit) {
            if($credit->expiresAt === NULL) {
                $withoutExpiration[] = $credit;
            } else {
                $withExpiration[] = $credit;
            }
        }
        $all = array_merge($withExpiration, $withoutExpiration);

        $remaining = $amount;
        foreach($all as $credit) {
            if($credit->amount <= $remaining) {
                $remaining -= $credit->amount;
                $credit->amount = 0;
            } else if($credit->amount > $remaining) {
                $credit->amount -= $remaining;
                $remaining = 0;
            }
        }

        return new CreditMovement(-$amount, $this, $notes, $application);
    }

    public function hasAppliedForEvent(Event $event) {
        return $this->countAppliedApplicationsForEvent($event) > 0;
    }

    public function countAppliedApplicationsForEvent(Event $event) {
        return $this->findAppliedApplicationsForEvent($event)->count();
    }

    /**
     * @param Event $event
     * @return ICollection|Application[]
     */
    public function findAcceptedApplicationsForEvent(Event $event) {
        return $this->acceptedApplications->findBy([
            'event' => $event,
            'canceledAt' => NULL,
            'rejectedAt' => NULL,
        ])->orderBy('id', ICollection::DESC);
    }

    protected function getterVisibleLetters() {
        return $this->letters->get()->findBy(['visible' => TRUE]);
    }

    public function findPendingReceivedLettersAtEvent(Event $event) {
        return $this->letters->get()->findBy([
            'visible' => FALSE,
            'direction' => Letter::DIRECTION_CHILD_TO_PARENT,
            'event' => $event,
        ]);
    }

    public function findLettersAtEvent(Event $event) {
        return $this->letters->get()->findBy([
            'event' => $event,
            'visible' => TRUE,
        ]);
    }

    /**
     * @param Event $event
     * @return ICollection|Application[]
     */
    public function findAppliedApplicationsForEvent(Event $event) {
        return $this->appliedApplications->findBy([
            'event' => $event,
            'canceledAt' => NULL,
            'rejectedAt' => NULL,
        ])->orderBy('id', ICollection::DESC);
    }

    function getUserDataForFacebookPixel() {
        $names = explode(' ', $this->name);
        $phone = $this->phone;
        if(empty($phone)) {
            $phone = NULL;
        } else {
            $phone = str_replace(' ', '', $phone);
            if(Strings::startsWith($phone, '+420')) {
                $phone = str_replace('+', '00', $phone);
            } else {
                $phone = '00420' . $phone;
            }
        }
        $gender = NULL;
        if($this->facebookGender === 'male') {
            $gender = 'm';
        } else if($this->facebookGender === 'female') {
            $gender = 'f';
        }
        return [
            'em' => $this->email, // email
            'fn' => Strings::lower($names[0]), // first name
            'ln' => isset($names[1]) ? Strings::lower($names[1]) : NULL, // last name
            'ph' => $phone, // phone
            //'ge' => $gender, // gender m/f
            //'ct' => empty($user->city) ? NULL : $user->city, // city
            //'st' => 'cz', // state
            //'zp' => empty($user->zip) ? NULL : $user->zip, // zip
        ];
    }

    protected function getterIsVip(): bool
    {
        return $this->vipLevel > 0;
    }

    static public function createFromArray(array $data) {
        $user = new self(
            $data['email'],
            $data['name']
        );
        $user->setValues($data);
        return $user;
    }

    function __toString() {
        return sprintf('#%s(%s)', $this->isPersisted() ? $this->id : 'null', $this->email);
    }

}
