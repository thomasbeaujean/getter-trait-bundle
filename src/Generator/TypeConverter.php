<?php

namespace Tbn\GetterTraitBundle\Generator;

use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\BackedEnumType;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\EnumType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\NullableType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\UnionType;

class TypeConverter
{
    public function convertType(Type $type): string
    {
        switch ($type::class) {
            case ObjectType::class:
                /** @var ObjectType $type */
                return '\\' . $type->getClassName();
            case BuiltinType::class:
                /** @var BuiltinType $type */
                return $type->__toString();
            case UnionType::class:
                /** @var UnionType $type */
                $types = $type->getTypes();
                $convertedTypes = [];

                foreach ($types as $subType) {
                    $convertedType = $this->convertType($subType);
                    // Deduplicate types (e.g., array<string, X> and array<int, Y> both become 'array')
                    if (!in_array($convertedType, $convertedTypes, true)) {
                        $convertedTypes[] = $convertedType;
                    }
                }

                return implode('|', $convertedTypes);
            case BackedEnumType::class:
                /** @var BackedEnumType $type */
                return '\\' . $type->getClassName();
            case EnumType::class:
                /** @var EnumType $type */
                return '\\' . $type->getClassName();
            case CollectionType::class:
                /** @var CollectionType $type */
                if ($type->isList()) {
                    return $this->convertType($type->getWrappedType());
                }

                return $this->convertType($type->getWrappedType());
            case GenericType::class:
                /** @var GenericType $type */
                return $this->convertType($type->getWrappedType());
            case NullableType::class:
                /** @var NullableType $type */
                return $this->convertType($type->getWrappedType()) . '|null';
        }

        throw new \LogicException('Class ' . $type::class . ' not handled');
    }

    public function convertCollectionDockblockType(CollectionType $type): string
    {
        $initString = $type->__toString();
        $initString = str_replace(',App', ',\\App', $initString);
        $initString = str_replace(', App', ', \\App', $initString);
        $initString = str_replace('<App', '<\\App', $initString);
        $initString = str_replace(',Tbn', ',\\Tbn', $initString);
        $initString = str_replace(', Tbn', ', \\Tbn', $initString);
        $initString = str_replace('Doctrine', '\\Doctrine', $initString);
        $initString = str_replace('Symfony', '\\Symfony', $initString);

        return $initString;
    }
}
