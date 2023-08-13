<?php

namespace VCD2\Credits;

use VCD2\Entity;
use VCD2\Users\User;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property User $user {m:1 User::$credits}
 * @property int $amount
 * @property string|NULL $notes
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable $addedAt {default now}
 * @property \DateTimeImmutable|NULL $expiresAt
 *
 *
 */
class Credit extends Entity {

    const EXPIRATION_REVIEW_REWARD = '+1 year'; // Platnost odměny za recenzi
    const EXPIRATION_APPLICATION_REJECTED_REFUND = '+1 year'; // Minimální platnost vrácených kreditů za zrušenou přihlášku

    const AMOUNT_REVIEW_REWARD = 100; // Odměna za recenzi

    function __construct(User $user, $amount, \DateTimeImmutable $expiresAt = NULL, $notes = NULL) {
        parent::__construct();

        $this->user = $user;
        $this->amount = $amount;
        $this->expiresAt = $expiresAt;
        $this->notes = $notes;
    }

}
