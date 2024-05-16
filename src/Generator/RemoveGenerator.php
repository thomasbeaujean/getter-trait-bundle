<?php

namespace Tbn\GetterTraitBundle\Generator;

use Symfony\Component\PropertyInfo\Type;

class RemoveGenerator extends AbstractPropertyGenerator
{
    private static string $template =
    '
    public function <methodName>(<type> $value): void
    {
        $this-><fieldName> = array_diff($this-><fieldName>, <valueDiff>);
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

        $methodType = $this->convertType($type);

        $replacements = [
            '<type>' => $methodType,
            '<methodName>' => $methodName,
            '<fieldName>' => $property,
            '<valueDiff>' => $methodType === 'array' ? '$value' : '[$value]',
        ];

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            static::$template
        );

        return $method;
    }
}
