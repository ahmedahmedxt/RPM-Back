<?php

namespace App\Entity;

use App\Repository\CollaborateurDocumentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollaborateurDocumentsRepository::class)]
#[ORM\Table(name: "collaborateurdocuments")]
class CollaborateurDocuments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "collaborateurDocumentsId")]
    private ?int $collaborateurDocumentsId = null;

    #[ORM\Column(name: "collaborateurDocumentsPdf", length: 254, nullable: true)]
    private ?string $collaborateurDocumentsPdf = null;

    #[ORM\ManyToOne(targetEntity: TypeDocument::class, inversedBy: 'collaborateurDocuments')]
    #[ORM\JoinColumn(name: "collaborateurDocumentsType", referencedColumnName: "typeDocumentId", nullable: true)]
    private ?TypeDocument $collaborateurDocumentsType = null;

    #[ORM\ManyToOne(targetEntity: Collaborateur::class, inversedBy: 'collaborateurDocuments')]
    #[ORM\JoinColumn(name: "collaborateurId", referencedColumnName: "collaborateurId", nullable: true)]
    private ?Collaborateur $collaborateur = null;

    public function getCollaborateurDocumentsId(): ?int
    {
        return $this->collaborateurDocumentsId;
    }

    public function getCollaborateurDocumentsPdf(): ?string
    {
        return $this->collaborateurDocumentsPdf;
    }

    public function setCollaborateurDocumentsPdf(?string $collaborateurDocumentsPdf): static
    {
        $this->collaborateurDocumentsPdf = $collaborateurDocumentsPdf;
        return $this;
    }

    public function getCollaborateurDocumentsType(): ?TypeDocument
    {
        return $this->collaborateurDocumentsType;
    }

    public function setCollaborateurDocumentsType(?TypeDocument $collaborateurDocumentsType): static
    {
        $this->collaborateurDocumentsType = $collaborateurDocumentsType;
        return $this;
    }

    public function getCollaborateur(): ?Collaborateur
    {
        return $this->collaborateur;
    }

    public function setCollaborateur(?Collaborateur $collaborateur): static
    {
        $this->collaborateur = $collaborateur;
        return $this;
    }
}