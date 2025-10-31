<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $message = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: "boolean")]
    private bool $isread = false;

    #[ORM\ManyToOne(targetEntity: AppelOffres::class)]
    #[ORM\JoinColumn(name: "appel_offre_id", referencedColumnName: "appelOffresId", nullable: true)]
    private ?AppelOffres $appelOffre = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getAppelOffre(): ?AppelOffres
    {
        return $this->appelOffre;
    }

    public function setAppelOffre(?AppelOffres $appelOffre): self
    {
        $this->appelOffre = $appelOffre;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isread;
    }

    public function setRead(bool $read): self
    {
        $this->isread = $read;
        return $this;
    }
}