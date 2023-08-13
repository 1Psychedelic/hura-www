<?php

namespace VCD\Notifications;

interface Notifications extends \Countable {

    const TYPE_MESSAGE = 0;
    const TYPE_APPLICATION = 1;
    const TYPE_USER = 2;
    const TYPE_BIRTHDAY = 3;
    const TYPE_NAMEDAY = 4;
    const TYPE_EVENT = 5;

    function add($message, $user = NULL, $related = NULL, $type = self::TYPE_MESSAGE, $push = false);

    function load();

    function count();

}
