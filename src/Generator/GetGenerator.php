<?php

namespace Tbn\GetterTraitBundle\Generator;

use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;

class GetGenerator
{
    private static string $template =
    '<dockblock>
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
            $initString = $this->typeConverter->convertCollectionDockblockType($type);

            $dockblock = <<< EOT
     /**
      * @return $initString
      */
 EOT;
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
