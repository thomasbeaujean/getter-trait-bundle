<?php

namespace tbn\GetterTraitBundle\Generator;

use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor as ExtractorReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

class ReflectionExtractor implements PropertyTypeExtractorInterface
{
    private const MAP_TYPES = [
        'integer' => Type::BUILTIN_TYPE_INT,
        'boolean' => Type::BUILTIN_TYPE_BOOL,
        'double' => Type::BUILTIN_TYPE_FLOAT,
    ];

    public function __construct(private readonly PhpStanExtractor $reflectionExtractor)
    {
    }

    public function getTypes(string $class, string $property, array $context = []): ?array
    {
        if ($type = $this->reflectionExtractor->getTypes($class, $property, $context)) {
            return $type;
        }
        if ($fromPropertyDeclaration = $this->extractFromPropertyDeclaration($class, $property)) {
            return $fromPropertyDeclaration;
        }

        return null;
    }


    private function extractFromPropertyDeclaration(string $class, string $property): ?array
    {
        try {
            $reflectionClass = new \ReflectionClass($class);

            $reflectionProperty = $reflectionClass->getProperty($property);
            $reflectionPropertyType = $reflectionProperty->getType();

            if (null !== $reflectionPropertyType && $types = $this->extractFromReflectionType($reflectionPropertyType, $reflectionProperty->getDeclaringClass())) {
                return $types;
            }
        } catch (\ReflectionException) {
            return null;
        }

        $defaultValue = $reflectionClass->getDefaultProperties()[$property] ?? null;

        if (null === $defaultValue) {
            return null;
        }

        $type = \gettype($defaultValue);
        $type = static::MAP_TYPES[$type] ?? $type;

        return [new Type($type, $this->isNullableProperty($class, $property), null, Type::BUILTIN_TYPE_ARRAY === $type)];
    }

    private function extractFromReflectionType(\ReflectionType $reflectionType, \ReflectionClass $declaringClass): array
    {
        $types = [];
        $nullable = $reflectionType->allowsNull();

        foreach (($reflectionType instanceof \ReflectionUnionType || $reflectionType instanceof \ReflectionIntersectionType) ? $reflectionType->getTypes() : [$reflectionType] as $type) {
            if (!$type instanceof \ReflectionNamedType) {
                // Nested composite types are not supported yet.
                return [];
            }

            $phpTypeOrClass = $type->getName();
            if ('null' === $phpTypeOrClass || 'mixed' === $phpTypeOrClass || 'never' === $phpTypeOrClass) {
                continue;
            }

            if (Type::BUILTIN_TYPE_ARRAY === $phpTypeOrClass) {
                $types[] = new Type(Type::BUILTIN_TYPE_ARRAY, $nullable, null, true);
            } elseif ('void' === $phpTypeOrClass) {
                $types[] = new Type(Type::BUILTIN_TYPE_NULL, $nullable);
            } elseif ($type->isBuiltin()) {
                $types[] = new Type($phpTypeOrClass, $nullable);
            } else {
                $types[] = new Type(Type::BUILTIN_TYPE_OBJECT, $nullable, $this->resolveTypeName($phpTypeOrClass, $declaringClass));
            }
        }

        return $types;
    }

    private function resolveTypeName(string $name, \ReflectionClass $declaringClass): string
    {
        if ('self' === $lcName = strtolower($name)) {
            return $declaringClass->name;
        }
        if ('parent' === $lcName && $parent = $declaringClass->getParentClass()) {
            return $parent->name;
        }

        return $name;
    }

    private function isNullableProperty(string $class, string $property): bool
    {
        try {
            $reflectionProperty = new \ReflectionProperty($class, $property);

            $reflectionPropertyType = $reflectionProperty->getType();

            return null !== $reflectionPropertyType && $reflectionPropertyType->allowsNull();
        } catch (\ReflectionException) {
            // Return false if the property doesn't exist
        }

        return false;
    }
}
