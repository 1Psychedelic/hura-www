<?php

namespace VCD2\Users\Service;

use Monolog\Logger;
use Nette\SmartObject;
use VCD2\Credits\Credit;
use VCD2\Credits\CreditMovement;
use VCD2\Orm;
use VCD2\Users\InsufficientCreditException;

/**
 * @method onCreditMovement(CreditMovement $movement)
 */
class Credits {

    use SmartObject;

    public $onCreditMovement = [];

    private $orm;

    /**
     * @var Logger
     */
    private $logger;

    function __construct(Orm $orm, Logger $logger) {
        $this->orm = $orm;
        $this->logger = $logger->withName('vcd.credits');
    }

    /**
     * @param $userId
     * @return int
     */
    function available($userId) {
        $user = $this->orm->users->get($userId);
        return $user === NULL ? 0 : $user->creditBalance;
    }

    function add($userId, $amount, \DateTimeImmutable $expiresAt = NULL, $notes = NULL) {
        $user = $this->orm->users->get($userId);
        if($user === NULL) {
            return;
        }

        $amount = abs($amount);

        $bucket = new Credit($user, $amount, $expiresAt, $notes);
        $this->orm->persist($bucket);

        $movement = new CreditMovement($amount, $user, $notes);
        $this->executeMovement($movement);
    }

    /**
     * @param $userId
     * @param $amount
     * @param $notes
     * @param null $applicationId
     * @throws InsufficientCreditException
     */
    function spend($userId, $amount, $notes, $applicationId = NULL) {
        $user = $this->orm->users->get($userId);
        if($user === NULL || intval($amount) === 0) {
            return;
        }
        
        try {
            $movement = $user->spendCredits($amount, $notes, $applicationId === NULL ? NULL : $this->orm->applications->get($applicationId));
        } catch (InsufficientCreditException $e) {
            $this->logger->notice(sprintf('Uživateli %s nemohlo být odečteno %s kreditů, protože by se dostal do mínusu (má na kontě pouze %s).', $user, abs($amount), $user->creditBalance));
            throw $e;
        }
        $this->executeMovement($movement);
    }

    function executeMovement(CreditMovement $movement) {
        if($movement->isPersisted()) {
            throw new \InvalidArgumentException('Given movement has already been executed.');
        }
        $balance = $movement->user->creditBalance - $movement->difference;
        $this->orm->persistAndFlush($movement);
        $this->logger->info(sprintf(
            'Provádím kreditovou transakci %s: uživatel %s měl %s Kč, po %s %s Kč má nyní %s Kč',
            $movement, $movement->user, $balance, $movement->isNegative ? 'odečtení' : 'přičtení', abs($movement->difference), $balance + $movement->difference
        ));
        $this->onCreditMovement($movement);
    }

}
