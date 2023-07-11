<?php

namespace tbn\GetterTraitBundle\Command;

use tbn\GetterTraitBundle\Attributes\AnnotationCollectionFactory;
use tbn\GetterTraitBundle\Generator\EntityGenerator;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use tbn\GetterTraitBundle\Generator\ReflectionExtractor;

#[AsCommand(name: 'generate:getter:traits')]
class EntitiesCommand extends Command
{
    public function __construct(
        private ReflectionExtractor $extractor,
        private EntityGenerator $entityGenerator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $factoryAnnotation = new AnnotationCollectionFactory(['src']);
        $classes = $factoryAnnotation->create();

        /** @var ReflectionClass $reflectionClass */
        foreach ($classes as $class => $reflectionClass) {
            foreach ($reflectionClass->getProperties() as $property) {
                $types = $this->extractor->getTypes($class, $property->getName());
            }

            $this->entityGenerator->writeEntityClass($reflectionClass, $types);
        }

        return Command::SUCCESS;
    }
}
