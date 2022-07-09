<?php

namespace tbn\GetterTraitBundle\Generator;

use Symfony\Component\PropertyInfo\Type;

class GetGenerator extends AbstractPropertyGenerator
{
    private static string $template =
    '
    public function <methodName>(): <nullable><type>
    {
        return $this-><fieldName>;
    }
';

    public function getMethodName(string $fieldName): string
    {
        return 'get'.$this->inflector->classify($fieldName);
    }

    public function generate(string $fieldName, Type $type): string
    {
        $methodName = $this->getMethodName($fieldName);

        $replacements = [
            '<type>' => $this->convertType($type),
            '<methodName>' => $methodName,
            '<fieldName>' => $fieldName,
            '<nullable>' => ($type->isNullable() ? '?':'')
        ];

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            static::$template
        );

        return $method;
    }
}
