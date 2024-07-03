<?php

namespace Tbn\GetterTraitBundle\Generator;

use Symfony\Component\TypeInfo\Type;

class SetGenerator
{
    private static string $template =
    '
    public function <methodName>(<type> $value): void
    {
        $this-><fieldName> = $value;
    }
';

    public function __construct(
        private TypeConverter $typeConverter,
    ) {
    }

    public function getMethodName(string $fieldName): string
    {
        return 'set'.ucfirst($fieldName);
    }

    public function generate(string $fieldName, Type $type): string
    {
        $methodName = $this->getMethodName($fieldName);

        $replacements = [
            '<type>' => $this->typeConverter->convertType($type),
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
