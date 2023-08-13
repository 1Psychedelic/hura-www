<?php

namespace VCD2\Utils;

use Hafo\Orm\Encryption\DatabaseCryptoHelper;
use Hafo\Security\SecurityException;
use Nette\Utils\Validators;
use Nextras\Dbal\Connection;
use VCD2\Orm;
use VCD2\Users\Service\AutomaticSignup;

class AutomaticSignupMigrationTool {

    private $connection;

    private $crypto;

    private $automaticSignup;

    function __construct(Connection $connection, DatabaseCryptoHelper $crypto, AutomaticSignup $automaticSignup) {
        $this->connection = $connection;
        $this->crypto = $crypto;
        $this->automaticSignup = $automaticSignup;
    }

    function createAccounts() {
        $encryptedEmails = $this->connection->query('SELECT DISTINCT encrypted_email FROM vcd_application')->fetchPairs(NULL, 'encrypted_email');
        $emails = array_map(function($email) {
            return $this->crypto->decrypt('vcd_application', 'encryptedEmail', $email);
        }, $encryptedEmails);

        $accountsCreated = [];

        foreach($emails as $email) {
            if(!Validators::isEmail($email)) {
                continue;
            }
            try {
                $this->automaticSignup->createAccount($email);
                $accountsCreated[$email] = TRUE;
            } catch (SecurityException $e) {
                $this->automaticSignup->pairApplications($email);
            }
        }

        return $accountsCreated;
    }

}
