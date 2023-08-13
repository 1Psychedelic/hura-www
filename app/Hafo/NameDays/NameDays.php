<?php

namespace Hafo\NameDays;

interface NameDays {

    /**
     * Returns array of names that are celebrating at given date.
     *
     * @param \DateTimeInterface $when
     * @return string[]
     */
    function namesAt(\DateTimeInterface $when);

    /**
     * Returns next celebration day for a given name.
     *
     * @param $name
     * @param \DateTimeInterface $since
     * @return \DateTimeInterface
     * @throws UnrecognizedNameException
     */
    function nameDayOf($name, \DateTimeInterface $since);

}
