<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'roleId')]
    private ?int $roleId = null;

    #[ORM\Column(name: "roleLibelle", length: 255, nullable: true)]
    private ?string $roleLibelle = null;

    #[ORM\Column(name: "roleShort",length: 255, nullable: true)]
    private ?string $roleShort = null;

    #[ORM\ManyToMany(targetEntity: Reference::class, mappedBy: 'roles')]
    private $references;

    public function __construct()
    {
        $this->references = new ArrayCollection();
    }

    public function getRoleId(): ?int
    {
        return $this->roleId;
    }

    public function getRoleLibelle(): ?string
    {
        return $this->roleLibelle;
    }

    public function setRoleLibelle(?string $roleLibelle): static
    {
        $this->roleLibelle = $roleLibelle;

        return $this;
    }

    public function getRoleShort(): ?string
    {
        return $this->roleShort;
    }

    public function setRoleShort(?string $roleShort): static
    {
        $this->roleShort = $roleShort;

        return $this;
    }

    /**
     * @return Collection|Reference[]
     */
    public function getReferences(): Collection
    {
        return $this->references;
    }

    public function addReference(Reference $reference): self
    {
        if (!$this->references->contains($reference)) {
            $this->references[] = $reference;
            $reference->addRole($this);
        }

        return $this;
    }

    public function removeReference(Reference $reference): self
    {
        if ($this->references->removeElement($reference)) {
            $reference->removeRole($this);
        }

        return $this;
    }
}
