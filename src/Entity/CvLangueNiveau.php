<?php

namespace App\Entity;

use App\Repository\CvLangueNiveauRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CvLangueNiveauRepository::class)]
#[ORM\Table(name: 'cvlangueniveau')]
class CvLangueNiveau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "cvLangueNiveauId")]
    private ?int $cvLangueNiveauId = null;

    #[ORM\Column(name: "cvLangueNiveauLibelle",length: 254, nullable: true)]
    private ?string $cvLangueNiveauLibelle = null;

    public function getCvLangueNiveauId(): ?int
    {
        return $this->cvLangueNiveauId;
    }

    public function getCvLangueNiveauLibelle(): ?string
    {
        return $this->cvLangueNiveauLibelle;
    }

    public function setCvLangueNiveauLibelle(?string $cvLangueNiveauLibelle): static
    {
        $this->cvLangueNiveauLibelle = $cvLangueNiveauLibelle;

        return $this;
    }
}
