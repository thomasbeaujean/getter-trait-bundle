<?php

namespace Tbn\GetterTraitBundle\Tests\src\Entity;

use Symfony\Component\Uid\Uuid;
use Tbn\GetterTraitBundle\Attributes\GetSetTrait;

#[GetSetTrait]
class ForeignClass
{
    private Uuid $id;
    private ?MyClass $myClass;
}
