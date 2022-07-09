<?php

namespace tbn\GetterTraitBundle\Generator;

use ReflectionClass;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

class ArrayConstructorGenerator
{
    protected static $template =
'
    public function arrayConstruct()
    {
        <collections>
    }
';

    public function __construct(private PropertyInfoExtractorInterface $extractor)
    {
    }

    public function generate(ReflectionClass $reflectionClass): string
    {
        $content = '';

        $collections = [];

        $properties = $this->extractor->getProperties($reflectionClass->getName());

        foreach ($properties as $property) {
            $types = $this->extractor->getTypes($reflectionClass->getName(), $property);
            /** @var Type $type */
            foreach ($types as $type) {
                if (!$type->isCollection()) {
                    continue;
                }
                $collections[] = '$this->'.$property.' = [];';
            }
        }

        if (count($collections) > 0) {
            $content = str_replace('<collections>', implode("\n        ", $collections), self::$template);
        }

        return $content;
    }
}
