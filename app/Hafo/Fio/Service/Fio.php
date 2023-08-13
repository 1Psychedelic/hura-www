<?php

namespace Hafo\Fio\Service;

use Hafo\Fio\FioException;
use Hafo\Fio\Payment;

interface Fio {

    /**
     * @param \DateTimeInterface $since
     * @param \DateTimeInterface $till
     * @return Payment[]
     * @throws FioException
     */
    function getTransactionsByPeriod(\DateTimeInterface $since, \DateTimeInterface $till);

}
