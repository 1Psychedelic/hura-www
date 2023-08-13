<?php

namespace Hafo\Gallery;

interface Gallery {

    /** @return Photo[] */
    function getPhotos();

    /** @return Photo */
    function getCurrentPhoto();

    /** @return Photo|NULL */
    function getPreviousPhoto();

    /** @return Photo|NULL */
    function getNextPhoto();

}
