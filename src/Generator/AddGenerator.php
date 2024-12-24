<?php

namespace Tbn\GetterTraitBundle\Generator;

use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;

class AddGenerator
{
    private EnglishInflector $inflector;

    private static string $beginTemplate =
    '
    public function <methodName>(<type> $value): void
    {';

    private static string $endTemplate =
    '
        $this-><fieldName>[] = $value;
    }
';

    public function __construct(
        private TypeConverter $typeConverter,

    ) {
        $this->inflector = new EnglishInflector();
    }

    public function getMethodName(string $fieldName): string
    {
        $values = $this->inflector->singularize($fieldName);

        return 'add'.ucfirst(end($values));
    }

    public function generate(
        string $entityName,
        string $property,
        CollectionType $type,
    ): string {
        $methodName = $this->getMethodName($property);

        $fieldType = match($type->getWrappedType()::class) {
            BuiltinType::class =>
                /** @var BuiltinType $type */
                 $type->__toString(),
            GenericType::class =>
                /** @var GenericType $type */
                $this->typeConverter->convertType($type->getWrappedType()->getVariableTypes()[1]),
            default =>
                /** @var GenericType $type */
                'mixed',
        };

        $replacements = [
            '<type>' => $fieldType,
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
