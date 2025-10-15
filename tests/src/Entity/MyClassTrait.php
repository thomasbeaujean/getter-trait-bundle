<?php
namespace Tbn\GetterTraitBundle\Tests\src\Entity;
trait MyClassTrait
{
    public function arrayConstruct(): void
    {
        $this->foreignClasses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->references = [];
    }

    public function __construct()
    {
        $this->arrayConstruct();
    }

    public function setId(\Symfony\Component\Uid\Uuid $value): void
    {
        $this->id = $value;
    }

    public function getId(): \Symfony\Component\Uid\Uuid
    {
        return $this->id;
    }

    public function setNumber(int $value): void
    {
        $this->number = $value;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setName(string $value): void
    {
        $this->name = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int|string, \Tbn\GetterTraitBundle\Tests\src\Entity\ForeignClass> $value
     */
    public function setForeignClasses(\Doctrine\Common\Collections\Collection $value): void
    {
        $this->foreignClasses = $value;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<int|string, \Tbn\GetterTraitBundle\Tests\src\Entity\ForeignClass>
     */
    public function getForeignClasses(): \Doctrine\Common\Collections\Collection
    {
        return $this->foreignClasses;
    }

    public function addForeignClass(\Tbn\GetterTraitBundle\Tests\src\Entity\ForeignClass $value): void
    {
        $this->foreignClasses->add($value);
    }

    public function removeForeignClass(\Tbn\GetterTraitBundle\Tests\src\Entity\ForeignClass $value): void
    {
        $this->foreignClasses->removeElement($value);
    }

    /**
     * @param list<int> $value
     */
    public function setReferences(array $value): void
    {
        $this->references = $value;
    }

    /**
     * @return list<int>
     */
    public function getReferences(): array
    {
        return $this->references;
    }

    public function addReference(int $value): void
    {
        $this->references[] = $value;
    }

    public function removeReference(int $value): void
    {
        $this->references = array_diff($this->references, [$value]);
    }
}
