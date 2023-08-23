<?php
namespace tbn\GetterTraitBundle\Tests\src\Entity;
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

    public function setMyClass(?\tbn\GetterTraitBundle\Tests\src\Entity\MyClass $value): void
    {
        $this->myClass = $value;
    }

    /**
     *
     */
    public function getMyClass(): ?\tbn\GetterTraitBundle\Tests\src\Entity\MyClass
    {
        return $this->myClass;
    }
}
