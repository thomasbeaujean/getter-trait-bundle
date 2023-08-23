<?php

namespace tbn\GetterTraitBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use tbn\GetterTraitBundle\Generator\Generator;

#[AsCommand(name: 'generate:getter:traits')]
class EntitiesCommand extends Command
{
    public function __construct(
        private Generator $generator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->generator->generate(['src']);

        return Command::SUCCESS;
    }
}
