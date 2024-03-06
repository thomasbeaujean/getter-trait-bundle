<?php

namespace Tbn\GetterTraitBundle\Generator;

use Symfony\Component\PropertyInfo\Type;

class RemoveGenerator extends AbstractPropertyGenerator
{
    private static string $template =
    '
    public function <methodName>(<type> $value): void
    {
        $this-><fieldName> = array_diff($this-><fieldName>, $value);
    }
';

    public function getMethodName(string $fieldName): string
    {
        $values = $this->inflector->singularize($fieldName);

        return 'remove'.ucfirst(end($values));
    }

    public function generate(string $property, Type $type): string
    {
        $methodName = $this->getMethodName($property);

        $replacements = [
            '<type>' => $this->convertType($type),
            '<methodName>' => $methodName,
            '<fieldName>' => $property,
        ];

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            static::$template
        );

        return $method;
    }
}
