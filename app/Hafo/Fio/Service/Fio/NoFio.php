<?php

namespace Hafo\Fio\Service\Fio;

class NoFio implements \Hafo\Fio\Service\Fio {

    function getTransactionsByPeriod(\DateTimeInterface $since, \DateTimeInterface $till) {
        return [];
    }

}
