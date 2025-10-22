<?php

namespace App\Entity;

use App\Repository\PartenaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PartenaireRepository::class)]
#[ORM\Table(name: 'partenaire')]
class Partenaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'partenaireId', type: 'integer')]
    #[Groups(['partenaire:read', 'appeloffre:read'])]
    private ?int $partenaireId = null;

    #[ORM\Column(name: 'partenaireLibelle', type: 'string', length: 255)]
    #[Groups(['partenaire:read', 'partenaire:write', 'appeloffre:read'])]
    private ?string $partenaireLibelle = null;

    #[ORM\Column(name: 'partenaireAcronyme', type: 'string', length: 50, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write', 'appeloffre:read'])]
    private ?string $partenaireAcronyme = null;

    #[ORM\Column(name: 'partenaireRole', type: 'string', length: 100, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write', 'appeloffre:read'])]
    private ?string $partenaireRole = null;

    #[ORM\ManyToMany(targetEntity: AppelOffre::class, mappedBy: 'partenaires')]
    private Collection $appelOffres;

    public function __construct()
    {
        $this->appelOffres = new ArrayCollection();
    }

    // Getters et Setters
    public function getPartenaireId(): ?int
    {
        return $this->partenaireId;
    }

    public function getPartenaireLibelle(): ?string
    {
        return $this->partenaireLibelle;
    }

    public function setPartenaireLibelle(string $partenaireLibelle): self
    {
        $this->partenaireLibelle = $partenaireLibelle;
        return $this;
    }

    public function getPartenaireAcronyme(): ?string
    {
        return $this->partenaireAcronyme;
    }

    public function setPartenaireAcronyme(?string $partenaireAcronyme): self
    {
        $this->partenaireAcronyme = $partenaireAcronyme;
        return $this;
    }

    public function getPartenaireRole(): ?string
    {
        return $this->partenaireRole;
    }

    public function setPartenaireRole(?string $partenaireRole): self
    {
        $this->partenaireRole = $partenaireRole;
        return $this;
    }

    /**
     * @return Collection<int, AppelOffre>
     */
    public function getAppelOffres(): Collection
    {
        return $this->appelOffres;
    }

    public function addAppelOffre(AppelOffre $appelOffre): self
    {
        if (!$this->appelOffres->contains($appelOffre)) {
            $this->appelOffres->add($appelOffre);
            $appelOffre->addPartenaire($this);
        }
        return $this;
    }

    public function removeAppelOffre(AppelOffre $appelOffre): self
    {
        if ($this->appelOffres->removeElement($appelOffre)) {
            $appelOffre->removePartenaire($this);
        }
        return $this;
    }
}