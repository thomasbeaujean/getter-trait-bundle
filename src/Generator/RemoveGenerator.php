<?php

namespace Tbn\GetterTraitBundle\Generator;

use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\ObjectType;

class RemoveGenerator
{
    private EnglishInflector $inflector;

    private static string $template =
        '
    public function <methodName>(<type> $value): void
    {
        $this-><fieldName> = array_diff($this-><fieldName>, [$value]);
    }
';

    private static string $templateForDoctrineCollection =
    '
    public function <methodName>(<type> $value): void
    {
        $this-><fieldName>->removeElement($value);
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

        return 'remove'.ucfirst(end($values));
    }

    public function generate(
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

        if ($type->getWrappedType() instanceof GenericType
            && $type->getWrappedType()->getWrappedType() instanceof ObjectType
            && $type->getWrappedType()->getWrappedType()->getClassName() === 'Doctrine\Common\Collections\Collection') {
            $selectedTemplate = static::$templateForDoctrineCollection;
        } else {
            $selectedTemplate = static::$template;
        }

        $replacements = [
            '<type>' => $fieldType,
            '<methodName>' => $methodName,
            '<fieldName>' => $property,
        ];

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $selectedTemplate
        );

        return $method;
    }
}
