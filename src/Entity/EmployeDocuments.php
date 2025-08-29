<?php

namespace App\Entity;

use App\Repository\EmployeDocumentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeDocumentsRepository::class)]
#[ORM\Table(name: "employedocuments")]
class EmployeDocuments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "employeDocumentsId")]
    private ?int $employeDocumentsId = null;

    #[ORM\Column(name: "employeDocumentsPdf", length: 254, nullable: true)]
    private ?string $employeDocumentsPdf = null;

    #[ORM\ManyToOne(inversedBy: 'employeDocuments')]
    #[ORM\JoinColumn(name: "employeDocumentsTypeId", referencedColumnName: "documentTypeId")]
    private ?TypeDocument $employeDocumentsType = null;

    #[ORM\ManyToOne(inversedBy: 'employeDocuments')]
    #[ORM\JoinColumn(name: "employeId", referencedColumnName: "employeId")]
    private ?Employe $employe = null;

    public function getEmployeDocumentsId(): ?int
    {
        return $this->employeDocumentsId;
    }

    public function getEmployeDocumentsPdf(): ?string
    {
        return $this->employeDocumentsPdf;
    }

    public function setEmployeDocumentsPdf(?string $employeDocumentsPdf): static
    {
        $this->employeDocumentsPdf = $employeDocumentsPdf;

        return $this;
    }

    public function getEmployeDocumentsType(): ?TypeDocument
    {
        return $this->employeDocumentsType;
    }

    public function setEmployeDocumentsType(?TypeDocument $employeDocumentsType): static
    {
        $this->employeDocumentsType = $employeDocumentsType;

        return $this;
    }

    public function getEmploye(): ?Employe
    {
        return $this->employe;
    }

    public function setEmploye(?Employe $employeId): static
    {
        $this->employe = $employeId;

        return $this;
    }
}
