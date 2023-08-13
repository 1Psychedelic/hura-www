<?php

namespace Hafo\Gallery\Photo;

use Hafo\Gallery;

class SimplePhoto implements Gallery\Photo {

    private $name;

    private $photoUrl;

    private $thumbnailUrl;

    private $thumbnailWidth;

    private $thumbnailHeight;

    function __construct($name, $photoUrl, $thumbnailUrl, $thumbnailWidth, $thumbnailHeight) {
        $this->name = $name;
        $this->photoUrl = $photoUrl;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->thumbnailWidth = $thumbnailWidth;
        $this->thumbnailHeight = $thumbnailHeight;
    }

    function getName() {
        return $this->name;
    }

    function getPhotoUrl() {
        return $this->photoUrl;
    }

    function getThumbnailUrl() {
        return $this->thumbnailUrl;
    }

    function getThumbnailWidth() {
        return $this->thumbnailWidth;
    }

    function getThumbnailHeight() {
        return $this->thumbnailHeight;
    }

}
