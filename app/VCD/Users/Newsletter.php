<?php

namespace VCD\Users;

interface Newsletter {

    function add($email);

    function remove($email);

    function isAdded($email);

}
