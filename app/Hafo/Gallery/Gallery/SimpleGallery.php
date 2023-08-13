<?php

namespace Hafo\Gallery\Gallery;

use Hafo\Gallery;
use Hafo\Gallery\Photo;

class SimpleGallery implements Gallery\Gallery {

    private $photos = [];

    private $currentPhotoName;

    private $currentPhoto;

    private $previousPhoto;

    private $nextPhoto;

    function __construct($currentPhotoName = NULL) {
        $this->currentPhotoName = $currentPhotoName;
    }

    function addPhoto(Photo $photo) {
        static $previous;
        static $grabNext;

        if($this->currentPhotoName === NULL && $previous === NULL) {
            $this->currentPhoto = $photo;
            $grabNext = TRUE;
        } else if($photo->getName() === $this->currentPhotoName) {
            $this->previousPhoto = $previous;
            $this->currentPhoto = $photo;
            $grabNext = TRUE;
        } else if($grabNext) {
            $this->nextPhoto = $photo;
            $grabNext = FALSE;
        }
        $previous = $photo;

        $this->photos[] = $photo;

        return $this;
    }

    function getPhotos() {
        return $this->photos;
    }

    function getCurrentPhoto() {
        return $this->currentPhoto;
    }

    function getPreviousPhoto() {
        return $this->previousPhoto;
    }

    function getNextPhoto() {
        return $this->nextPhoto;
    }

}
