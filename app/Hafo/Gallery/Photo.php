<?php

namespace Hafo\Gallery;

interface Photo {

    function getName();

    function getPhotoUrl();

    function getThumbnailUrl();

    function getThumbnailWidth();

    function getThumbnailHeight();

}
