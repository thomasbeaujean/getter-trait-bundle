<?php

namespace tbn\GetterTraitBundle\Tests\src\Entity;

use Symfony\Component\Uid\Uuid;
use tbn\GetterTraitBundle\Attributes\GetSetTrait;

#[GetSetTrait]
class ForeignClass
{
    private Uuid $id;
    private ?MyClass $myClass;
}
