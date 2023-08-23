<?php

namespace tbn\GetterTraitBundle\Generator;

use ReflectionClass;
use tbn\GetterTraitBundle\Attributes\AnnotationCollectionFactory;
use tbn\GetterTraitBundle\Generator\EntityGenerator;
use tbn\GetterTraitBundle\Generator\ReflectionExtractor;

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
