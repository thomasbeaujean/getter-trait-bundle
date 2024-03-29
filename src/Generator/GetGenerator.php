<?php

namespace Tbn\GetterTraitBundle\Generator;

use Symfony\Component\PropertyInfo\Type;

class GetGenerator extends AbstractPropertyGenerator
{
    private static string $template =
    '
    /**
     * <dockblock>
     */
    public function <methodName>(): <nullable><type>
    {
        return $this-><fieldName>;
    }
';

    public function getMethodName(string $fieldName): string
    {
        return 'get'.ucfirst($fieldName);
    }

    public function generate(string $fieldName, Type $type): string
    {
        $methodName = $this->getMethodName($fieldName);
        $dockblock = '';

        $convertedType = $this->convertType($type);

        if ($convertedType === 'array') {
            $types = $type->getCollectionValueTypes();
            if (count($types) > 0) {
                $valueType = $type->getCollectionValueTypes()[0];
                $dockblock = $this->getDockblock($valueType);
            }
        }

        $replacements = [
            '<dockblock>' => $dockblock,
            '<type>' => $convertedType,
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

    private function getDockblock(Type $valueType): string
    {
        $nullString = '';
        if($valueType->isNullable()) {
            $nullString = '?';
        }

        if ($valueType->getBuiltinType() === 'object') {
            $itemType = $valueType->getClassName();
            return sprintf('@return %s\%s[]', $nullString, $itemType);
        }

        $itemType = $valueType->getBuiltinType();
        return sprintf('@return %s%s[]', $nullString, $itemType);
    }
}
