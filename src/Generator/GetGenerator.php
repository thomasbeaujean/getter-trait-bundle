<?php

namespace Tbn\GetterTraitBundle\Generator;

use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;

class GetGenerator
{
    private static string $template =
    '
    /**
     * <dockblock>
     */
    public function <methodName>(): <type>
    {
        return $this-><fieldName>;
    }
';

    public function __construct(
        private TypeConverter $typeConverter,
    ) {
    }

    public function getMethodName(string $fieldName): string
    {
        return 'get'.ucfirst($fieldName);
    }

    public function generate(string $fieldName, Type $type): string
    {
        $methodName = $this->getMethodName($fieldName);
        $dockblock = '';

        $convertedType = $this->typeConverter->convertType($type);

        if ($type instanceof CollectionType) {
            $initString = $type->__toString();
            $initString = str_replace(',App', ',\\App', $initString);
            $initString = str_replace(',Tbn', ',\\Tbn', $initString);
            $initString = str_replace('Doctrine', '\\Doctrine', $initString);
            $initString = str_replace('Symfony', '\\Symfony', $initString);

            $dockblock = "@return $initString";
        }

        $replacements = [
            '<dockblock>' => $dockblock,
            '<type>' => $convertedType,
            '<methodName>' => $methodName,
            '<fieldName>' => $fieldName,
        ];

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            static::$template
        );

        return $method;
    }
}
