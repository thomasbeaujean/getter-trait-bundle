<?php

namespace Tbn\GetterTraitBundle\Tests\src\Entity;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Tbn\GetterTraitBundle\Attributes\GetSetTrait;

#[GetSetTrait]
class MyClass
{
    private Uuid $id;
    private int $number;
    private string $name;

    /**
     * @var Collection<ForeignClass>
     */
    private Collection $foreignClasses;

    /**
     * @var array<int>
     */
    private array $references;
}
