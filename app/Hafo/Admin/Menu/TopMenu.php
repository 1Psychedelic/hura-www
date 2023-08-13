<?php

namespace Hafo\Admin\Menu;

interface TopMenu extends \Countable {

    /** @return SubMenu */
    function getItems();

}
