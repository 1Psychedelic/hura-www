<?php

namespace Hafo\Orm\Entity\Reflection;

use Nextras\Orm\Entity\Reflection\IMetadataParser;
use Nextras\Orm\Entity\Reflection\IMetadataParserFactory;
use Nextras\Orm\Entity\Reflection\MetadataParser;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;

class MetadataParserFactory implements IMetadataParserFactory {

    /**
     * @var Modifier[]
     */
    private $modifiers = [];

    function addModifier(Modifier $modifier) {
        $this->modifiers = $modifier;
    }

    public function create(array $entityClassesMap) : IMetadataParser {
        $p = new MetadataParser($entityClassesMap);
        foreach($this->modifiers as $modifier) {
            $p->addModifier($modifier->getName(), function(PropertyMetadata $property, array &$args) use ($modifier) {
                $modifier->parseMetadata($property, $args);
            });
        }
        return $p;
    }

}
