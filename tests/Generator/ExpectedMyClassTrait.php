<?php
namespace tbn\GetterTraitBundle\Tests\src\Entity;
trait MyClassTrait
{
    public function arrayConstruct()
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

    /**
     *
     */
    public function getId(): \Symfony\Component\Uid\Uuid
    {
        return $this->id;
    }

    public function setNumber(int $value): void
    {
        $this->number = $value;
    }

    /**
     *
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    public function setName(string $value): void
    {
        $this->name = $value;
    }

    /**
     *
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function setForeignClasses(\Doctrine\Common\Collections\Collection $value): void
    {
        $this->foreignClasses = $value;
    }

    /**
     *
     */
    public function getForeignClasses(): \Doctrine\Common\Collections\Collection
    {
        return $this->foreignClasses;
    }

    public function addForeignClass(\tbn\GetterTraitBundle\Tests\src\Entity\ForeignClass $value): void
    {
        $this->foreignClasses[] = $value;
    }

    public function removeForeignClass(\tbn\GetterTraitBundle\Tests\src\Entity\ForeignClass $value): void
    {
        $this->foreignClasses = array_diff($this->foreignClasses, $value);
    }

    public function setReferences(array $value): void
    {
        $this->references = $value;
    }

    /**
     * @var int[]
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
        $this->references = array_diff($this->references, $value);
    }
}
