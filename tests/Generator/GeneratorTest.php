<?php

namespace Tbn\GetterTraitBundle\Tests\Generator;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Nyholm\BundleTest\TestKernel;
use Acme\Service\Foo;
use Symfony\Component\HttpKernel\KernelInterface;
use Tbn\GetterTraitBundle\Generator\EntityGenerator;
use Tbn\GetterTraitBundle\Generator\Generator;

class GeneratorTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */

        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(\Tbn\GetterTraitBundle\GetterTraitBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testGenerate(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $this->assertTrue($container->has(Generator::class));

        /** @var Generator */
        $service = $container->get(Generator::class);
        $service->generate(['./tests/src']);

        $this->assertGeneratedFile('MyClassTrait');
        $this->assertGeneratedFile('ForeignClassTrait');
    }

    private function assertGeneratedFile(string $filename): void
    {
        $expectedForeignClassTrait = \file_get_contents(__DIR__.'/Expected'.$filename.'.php');
        $foreignClassTrait = \file_get_contents(__DIR__.'/../src/Entity/'.$filename.'.php');

        // line kept for dev purpose
        // file_put_contents(__DIR__.'/Expected'.$filename.'.php', $foreignClassTrait);

        $this->assertSame($expectedForeignClassTrait, $foreignClassTrait);
    }
}
