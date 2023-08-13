<?php

namespace Hafo\Ares;

interface Ares
{

    /**
     * @param string $ico
     * @return Subject|NULL
     * @throws AresException
     */
    public function getSubjectByIco($ico);

}
