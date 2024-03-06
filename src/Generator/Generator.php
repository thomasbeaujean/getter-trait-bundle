<?php

namespace Tbn\GetterTraitBundle\Generator;

use ReflectionClass;
use Tbn\GetterTraitBundle\Attributes\AnnotationCollectionFactory;
use Tbn\GetterTraitBundle\Generator\EntityGenerator;
use Tbn\GetterTraitBundle\Generator\ReflectionExtractor;

class Generator
{
    public function __construct(
        private ReflectionExtractor $extractor,
        private EntityGenerator $entityGenerator
    ) {
    }

    public function generate(array $directories): void
    {
        $factoryAnnotation = new AnnotationCollectionFactory($directories);
        $classes = $factoryAnnotation->create();

        /** @var ReflectionClass $reflectionClass */
        foreach ($classes as $class => $reflectionClass) {
            foreach ($reflectionClass->getProperties() as $property) {
                $types = $this->extractor->getTypes($class, $property->getName());
            }

            $this->entityGenerator->writeEntityClass($reflectionClass, $types);
        }
    }
}
