<?php

namespace VCD2\Users;

use VCD2\Entity;
use VCD2\Users\EntityTrait\Consent\ConsentInfo;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property int $type {enum self::TYPE_*}
 * @property User|NULL $user {m:1 User::$consents}
 * @property string $consentText
 *
 *
 **** Osobní údaje
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable $consentedAt {default now}
 * @property \DateTimeImmutable $expiresAt {virtual}
 *
 * 
 */
class Consent extends Entity {

    use ConsentInfo;

    const TYPE_REGISTRATION = 0;
    const TYPE_PERSONAL_INFORMATION = 1;
    const TYPE_PHOTOGRAPHY = 2;
    const TYPE_EMAIL_MARKETING = 3;
    const TYPE_REVIEW = 4;
    const TYPE_CONTACT_FORM = 5;
    const TYPE_RECRUITMENT = 6;
    const TYPE_EBOOK_DOWNLOAD = 7;
    const TYPE_SMS_MARKETING = 8;
    const TYPE_TERMS_AND_CONDITIONS = 9;
    const TYPE_PARENT_GUIDELINE = 10;

    const TYPES_NAMES = [
        self::TYPE_REGISTRATION => 'Registrace',
        self::TYPE_PERSONAL_INFORMATION => 'Sběr osobních údajů',
        self::TYPE_PHOTOGRAPHY => 'Fotografování',
        self::TYPE_EMAIL_MARKETING => 'E-mailový marketing',
        self::TYPE_REVIEW => 'Recenze',
        self::TYPE_CONTACT_FORM => 'Kontaktní formulář',
        self::TYPE_RECRUITMENT => 'Nábor',
        self::TYPE_EBOOK_DOWNLOAD => 'Stažení e-booku',
        self::TYPE_SMS_MARKETING => 'SMS marketing',
        self::TYPE_TERMS_AND_CONDITIONS => 'Všeobecné obchodní podmínky',
        self::TYPE_PARENT_GUIDELINE => 'Jak to u nás chodí',
    ];

    const EMAIL_TYPES = [
        self::TYPE_EMAIL_MARKETING,
        self::TYPE_EBOOK_DOWNLOAD,
    ];

    const DOCUMENT_URL = 'https://včd.eu/GDPR';

    const CONSENT_EXPIRATION = '+10 years';

    function __construct($type, $consentText, $ip, User $user = NULL, $email = NULL) {
        parent::__construct();

        $this->type = $type;
        $this->consentText = $consentText;
        $this->user = $user;
        $this->email = $email;
        $this->ip = $ip;
    }

    protected function getterExpiresAt() {
        return $this->consentedAt->modify(self::CONSENT_EXPIRATION);
    }

}
