<?php

namespace Tbn\GetterTraitBundle\Generator;

use ReflectionClass;
use Symfony\Component\TypeInfo\Type\CollectionType;

class ArrayConstructorGenerator
{
    protected static $template =
'
    public function arrayConstruct()
    {
        <collections>
    }
';

    public function __construct(
        private Extractor $extractor,
        private TypeConverter $typeConverter,
    ) {
    }

    public function generate(ReflectionClass $reflectionClass): string
    {
        $content = '';

        $collections = [];

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $type = $this->extractor->getType($reflectionClass->getName(), $propertyName);

            if (null === $type) {
                continue;
            }
            if ($type->isNullable()) {
                continue;
            }

            if (! ($type instanceof CollectionType)) {
                continue;
            }

            $converted = $this->typeConverter->convertType($type);

            $initString = match ($converted) {
                'array' => '[]',
                '\Doctrine\Common\Collections\Collection' => 'new \Doctrine\Common\Collections\ArrayCollection()',
                default => 'new '.$converted.'()',
            };

            $collections[] = '$this->'.$propertyName.' = '.$initString.';';
        }

        if (count($collections) > 0) {
            $content = str_replace('<collections>', implode("\n        ", $collections), self::$template);
        }

        return $content;
    }
}
