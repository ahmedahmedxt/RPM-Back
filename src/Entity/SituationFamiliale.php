<?php

namespace App\Entity;

use App\Repository\SituationFamilialeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SituationFamilialeRepository::class)]
#[ORM\Table(name: 'situationfamiliale')]
class SituationFamiliale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "situationFamilialeId")]
    private ?int $situationFamilialeId = null;
    
    #[ORM\Column(name: "situationFamilialeLibelle", length: 254, unique: true)]
    #[Assert\NotBlank]
    private ?string $situationFamilialeLibelle = null;

    public function getSituationFamilialeId(): ?int
    {
        return $this->situationFamilialeId;
    }

    public function getSituationFamilialeLibelle(): ?string
    {
        return $this->situationFamilialeLibelle;
    }

    public function setSituationFamilialeLibelle(?string $situationFamiliale): static
    {
        $this->situationFamilialeLibelle = $situationFamiliale;

        return $this;
    }
}
