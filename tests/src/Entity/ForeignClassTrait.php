<?php
namespace Tbn\GetterTraitBundle\Tests\src\Entity;
trait ForeignClassTrait
{

    public function setId(\Symfony\Component\Uid\Uuid $value): void
    {
        $this->id = $value;
    }

    /**
     *
     */
    public function getId(): \Symfony\Component\Uid\Uuid
    {
        return $this->id;
    }

    public function setMyClass(?\Tbn\GetterTraitBundle\Tests\src\Entity\MyClass $value): void
    {
        $this->myClass = $value;
    }

    /**
     *
     */
    public function getMyClass(): ?\Tbn\GetterTraitBundle\Tests\src\Entity\MyClass
    {
        return $this->myClass;
    }
}
