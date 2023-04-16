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

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $types = $this->extractor->getTypes($reflectionClass->getName(), $propertyName);
            /** @var Type $type */
            foreach ($types as $type) {
                if (!$type->isCollection()) {
                    continue;
                }

                if ($type->isNullable()) {
                    continue;
                }

                $initString = '[]';
                if ($type->getBuiltinType() === 'object' && $type->getClassName() === 'Doctrine\Common\Collections\Collection') {
                    $initString = 'new \\Doctrine\\Common\\Collections\\ArrayCollection()';
                }

                $collections[] = '$this->'.$propertyName.' = '.$initString.';';
            }
        }

        if (count($collections) > 0) {
            $content = str_replace('<collections>', implode("\n        ", $collections), self::$template);
        }

        return $content;
    }
}
