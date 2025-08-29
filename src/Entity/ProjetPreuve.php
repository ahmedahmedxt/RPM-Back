<?php

namespace App\Entity;

use App\Repository\ProjetPreuveRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: ProjetPreuveRepository::class)]
class ProjetPreuve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $projetPreuveLibelle = null;

    /*
    #[ORM\ManyToOne(targetEntity: Projet::class)]
    #[Assert\NotBlank]
    private ?Projet $projet ;
    */

    #[ORM\OneToMany(mappedBy: 'projetPreuve', targetEntity: UploadFile::class,cascade: ["persist","remove"])]
    private Collection $uploadFiles;

    public function __construct()
    {
        $this->uploadFiles = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjetPreuveLibelle(): ?string
    {
        return $this->projetPreuveLibelle;
    }

    public function setProjetPreuveLibelle(string $projetPreuveLibelle): static
    {
        $this->projetPreuveLibelle = $projetPreuveLibelle;

        return $this;
    }
    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }
    /**
     * @return Collection|UploadFile[]
     */
    public function getUploadFiles(): Collection
    {
        return $this->uploadFiles;
    }

    public function addUploadFile(UploadFile $uploadFile): self
    {
        if (!$this->uploadFiles->contains($uploadFile)) {
            $this->uploadFiles[] = $uploadFile;
            $uploadFile->setProjetPreuve($this);
        }

        return $this;
    }

    public function removeUploadFile(UploadFile $uploadFile): self
    {
        if ($this->uploadFiles->removeElement($uploadFile)) {
            // set the owning side to null (unless already changed)
            if ($uploadFile->getProjetPreuve() === $this) {
                $uploadFile->setProjetPreuve(null);
            }
        }

        return $this;
    }
}
