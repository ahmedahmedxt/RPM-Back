<?php

namespace App\Entity;

use App\Repository\SourceAppelOffresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Pays;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SourceAppelOffresRepository::class)]
#[ORM\Table(name: 'sourceappeloffres')]
class SourceAppelOffres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'sourceAppelOffresId', type: 'integer')]
    private ?int $sourceAppelOffresId = null;

    #[ORM\Column(name: 'sourceAppelOffresLibelle', type: 'string', length: 50)]
    private ?string $sourceAppelOffresLibelle = null;

    #[ORM\Column(name: 'sourceAppelOffresDescription', type: 'text', nullable: true)]
    private ?string $sourceAppelOffresDescription = null;

    #[ORM\Column(name: 'sourceAppelOffresUrl', type: 'string', length: 50, nullable: true)]
    private ?string $sourceAppelOffresUrl = null;

    #[ORM\OneToMany(mappedBy: 'sourceAppelOffres', targetEntity: AppelOffres::class)]
    private Collection $appelOffres;

    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(name: 'paysId', referencedColumnName: 'paysId', nullable: true)]
    private ?Pays $pays = null;

    public function __construct()
    {
        $this->appelOffres = new ArrayCollection();
    }

    public function getSourceAppelOffresId(): ?int
    {
        return $this->sourceAppelOffresId;
    }

    public function getSourceAppelOffresLibelle(): ?string
    {
        return $this->sourceAppelOffresLibelle;
    }

    public function setSourceAppelOffresLibelle(?string $libelle): self
    {
        $this->sourceAppelOffresLibelle = $libelle;
        return $this;
    }

    public function getSourceAppelOffresDescription(): ?string
    {
        return $this->sourceAppelOffresDescription;
    }

    public function setSourceAppelOffresDescription(?string $description): self
    {
        $this->sourceAppelOffresDescription = $description;
        return $this;
    }

    public function getSourceAppelOffresUrl(): ?string
    {
        return $this->sourceAppelOffresUrl;
    }

    public function setSourceAppelOffresUrl(?string $url): self
    {
        $this->sourceAppelOffresUrl = $url;
        return $this;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): self
    {
        $this->pays = $pays;
        return $this;
    }


    /**
     * @return Collection<int, AppelOffres>
     */
    public function getAppelOffres(): Collection
    {
        return $this->appelOffres;
    }

    public function addAppelOffre(AppelOffres $appelOffre): self
    {
        if (!$this->appelOffres->contains($appelOffre)) {
            $this->appelOffres[] = $appelOffre;
            $appelOffre->setSourceAppelOffres($this);
        }
        return $this;
    }

    public function removeAppelOffre(AppelOffres $appelOffre): self
    {
        if ($this->appelOffres->removeElement($appelOffre)) {
            if ($appelOffre->getSourceAppelOffres() === $this) {
                $appelOffre->setSourceAppelOffres(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->sourceAppelOffresLibelle ?? 'SourceAppelOffres';
    }
}
