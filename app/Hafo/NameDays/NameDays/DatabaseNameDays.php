<?php

namespace Hafo\NameDays\NameDays;

use Hafo\NameDays;
use Nette\Database\Context;

final class DatabaseNameDays implements NameDays\NameDays {

    private $database;

    function __construct(Context $database) {
        $this->database = $database;
    }
    
    function namesAt(\DateTimeInterface $when) {
        return $this->database->table('name_days')
            ->where('day = ? AND month = ?', [$when->format('j'), $when->format('n')])
            ->fetchPairs(NULL, 'name');
    }

    function nameDayOf($name, \DateTimeInterface $since) {
        $row = $this->database->table('name_days')->where('name', $name)->fetch();
        if(!$row) {
            throw new NameDays\UnrecognizedNameException;
        }

        $ref = (new \DateTime)->setDate($since->format('Y'), $row['month'], $row['day']);
        if($ref < $since) {
            $ref->modify('+1 year');
        }
        return $ref;
    }
    
}
