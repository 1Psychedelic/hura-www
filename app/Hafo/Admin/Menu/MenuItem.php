<?php

namespace Hafo\Admin\Menu;

use Nette\Utils\Html;

interface MenuItem {

    /** @return string */
    function getLabel();

    /** @return string */
    function getHref();

    /** @return string */
    function getColor();

    /** @return Html */
    function getIcon();

    /** @return string|int|NULL */
    function getInformation();

    /** @return string|NULL */
    function getInformationClass();

    /** @return bool */
    function getTargetBlank();

}
