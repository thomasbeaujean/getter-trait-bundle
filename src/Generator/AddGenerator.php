<?php

namespace Tbn\GetterTraitBundle\Generator;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\ObjectType;

class AddGenerator
{
    private Inflector $inflector;

    private static string $beginTemplate =
    '
    public function <methodName>(<type> $value): void
    {';

    private static string $endTemplate =
        '
        $this-><fieldName>[] = $value;
    }
';

    private static string $endTemplateForDoctrineCollection =
        '
        $this-><fieldName>->add($value);
    }
';

    public function __construct(
        private TypeConverter $typeConverter,

    ) {
        $this->inflector = InflectorFactory::create()->build();
    }

    public function getMethodName(string $fieldName): string
    {
        $singular = $this->inflector->singularize($fieldName);

        return 'add'.ucfirst($singular);
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

        if ($type->getWrappedType() instanceof GenericType
            && $type->getWrappedType()->getWrappedType() instanceof ObjectType
            && $type->getWrappedType()->getWrappedType()->getClassName() === 'Doctrine\Common\Collections\Collection') {
            $selectedEndTemplate = static::$endTemplateForDoctrineCollection;
        } else {
            $selectedEndTemplate = static::$endTemplate;
        }

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
            $selectedEndTemplate
        );

        return $method;
    }
}
