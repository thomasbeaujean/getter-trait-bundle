<?php

namespace Tbn\GetterTraitBundle\Tests\Attributes;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tbn\GetterTraitBundle\Attributes\AnnotationCollectionFactory;
use Tbn\GetterTraitBundle\Attributes\GetSetTrait;
use Tbn\GetterTraitBundle\Tests\src\Entity\MyClass;

class AnnotationCollectionFactoryTest extends KernelTestCase
{
    public function testCreate(): void
    {
        $factoryAnnotation = new AnnotationCollectionFactory(['./tests/src']);
        $classes = $factoryAnnotation->create();

        $this->assertCount(2, $classes);
        $this->assertArrayHasKey(MyClass::class, $classes);
        $firstClass = $classes[MyClass::class];

        $this->assertCount(5, $firstClass->getProperties());
        $this->assertCount(1, $firstClass->getAttributes());
        $this->assertSame(GetSetTrait::class, $firstClass->getAttributes()[0]->getName());
    }
}
