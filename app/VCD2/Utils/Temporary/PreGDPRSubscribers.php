<?php

namespace VCD2\Utils\Temporary;

use Nette\Database\Context;
use VCD2\Orm;

class PreGDPRSubscribers {

    const DELETE_BEFORE = '2018-05-26';

    private $orm;

    private $db;

    function __construct(Orm $orm, Context $db) {
        $this->orm = $orm;
        $this->db = $db;
    }

    function findEmailsToSkip() {
        $subscribers = $this->db->table('vcd_newsletter')->where('added_at < ?', new \DateTime(self::DELETE_BEFORE))->fetchAll();
        $skip = [];
        foreach($subscribers as $subscriber) {
            $email = $subscriber['email'];
            $applications = $this->orm->applications->findByEmail($email);
            foreach($applications as $application) {
                if($application->isAccepted) {
                    $skip[] = $email;
                    break;
                }
            }
        }

        return $skip;
    }

    function delete() {
        $skip = $this->findEmailsToSkip();
        return $this->db->table('vcd_newsletter')->where('added_at < ? AND email NOT IN ?', new \DateTime(self::DELETE_BEFORE), $skip)->delete();
    }

}
