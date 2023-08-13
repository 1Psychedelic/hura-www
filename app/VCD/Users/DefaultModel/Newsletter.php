<?php

namespace VCD\Users\DefaultModel;

use Nette\Database\Context;
use Nette\SmartObject;
use VCD\Users;

/**
 * @method onAdd($email)
 * @method onRemove($email)
 */
class Newsletter implements Users\Newsletter {

    use SmartObject;

    public $onAdd = [];

    public $onRemove = [];

    private $database;

    function __construct(Context $database) {
        $this->database = $database;
    }

    function add($email) {
        if($this->isAdded($email)) {
            return;
        }
        $this->database->table('vcd_newsletter')->insert([
            'email' => $email,
            'added_at' => new \DateTime
        ]);
        $this->onAdd($email);
    }

    function remove($email) {
        $this->database->table('vcd_newsletter')->where('email', $email)->delete();
        $this->onRemove($email);
    }

    function isAdded($email) {
        $row = $this->database->table('vcd_newsletter')->where('email', $email)->fetch();
        if($row === FALSE) {
            return FALSE;
        }
        return TRUE;
    }

}
