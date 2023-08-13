<?php

namespace Hafo\Ares;

class Subject
{

    private $ico;

    private $dic;

    private $name;

    private $street;

    private $city;

    private $zip;

    function __construct($ico, $dic, $name, $street, $city, $zip)
    {
        $this->ico = $ico;
        $this->dic = $dic;
        $this->name = $name;
        $this->street = $street;
        $this->city = $city;
        $this->zip = $zip;
    }

    function getIco()
    {
        return $this->ico;
    }

    function getDic()
    {
        return $this->dic;
    }

    function getName()
    {
        return $this->name;
    }

    function getStreet()
    {
        return $this->street;
    }

    function getCity()
    {
        return $this->city;
    }

    function getZip()
    {
        return $this->zip;
    }

    static function createFromArray(array $data) {
        return new self(
            $data['ico'],
            $data['dic'],
            $data['name'],
            $data['street'],
            $data['city'],
            $data['zip']
        );
    }

}
