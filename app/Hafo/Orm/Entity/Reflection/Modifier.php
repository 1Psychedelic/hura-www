<?php

namespace Hafo\Orm\Entity\Reflection;

use Nextras\Orm\Entity\Reflection\PropertyMetadata;

interface Modifier {

    function getName();

    function parseMetadata(PropertyMetadata $property, array &$args);

}
