<?php declare(strict_types=1);

namespace Tbn\GetterTraitBundle\Attributes;

class AnnotationCollectionFactory
{
    public function __construct(private array $paths)
    {
    }

    public function create(): array
    {
        $classes = [];

        foreach (ReflectionClassRecursiveIterator::getReflectionClassesFromDirectories($this->paths) as $className => $reflectionClass) {
            if ($reflectionClass->getAttributes(GetSetTrait::class)) {
                $classes[$className] = $reflectionClass;
            }
        }

        return $classes;
    }
}
