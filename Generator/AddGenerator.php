<?php

namespace tbn\GetterTraitBundle\Generator;

use Symfony\Component\PropertyInfo\Type;

class AddGenerator extends AbstractPropertyGenerator
{
    private static string $beginTemplate =
    '
    public function <methodName>(<type> $value): void
    {';

    private static string $endTemplate =
    '
        $this-><fieldName>[] = $value;
    }
';

    public function getMethodName(string $fieldName): string
    {
        $methodName = 'add'.$this->inflector->classify($fieldName);

        return $this->inflector->singularize($methodName);
    }

    public function generate(string $entityName, string $property, Type $type): string
    {
        $methodName = $this->getMethodName($property);

        $replacements = [
            '<type>' => $this->convertType($type),
            '<methodName>' => $methodName,
            '<fieldName>' => $property,
            '<entityName>' => ucfirst($entityName)
        ];

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            static::$beginTemplate
        );

        $method .= str_replace(
            array_keys($replacements),
            array_values($replacements),
            static::$endTemplate
        );

        return $method;
    }
}
