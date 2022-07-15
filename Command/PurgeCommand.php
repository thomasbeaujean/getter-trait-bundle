<?php

namespace tbn\GetterTraitBundle\Command;

use tbn\GetterTraitBundle\Attributes\AnnotationCollectionFactory;
use tbn\GetterTraitBundle\Generator\EntityGenerator;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

#[AsCommand(name: 'generate:getter:purge')]
class PurgeCommand extends Command
{
    public function __construct(
        private PropertyInfoExtractorInterface $extractor,
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
            $this->entityGenerator->purgeTrait($reflectionClass);
        }

        return Command::SUCCESS;
    }
}
