<?php

namespace Hafo\Admin\Menu;

interface SubMenu extends \Countable {

    /** @return string */
    function getLabel();

    /** @return MenuItem */
    function getItems();

    /** @return bool */
    function needsAttention();

}
