<?php

namespace App\Entity;

use App\Repository\DateChangeNotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DateChangeNotificationRepository::class)]
class DateChangeNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dateChangeNotifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fiche $fiche = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $oldDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $newDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column]
    private bool $isRead = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFiche(): ?Fiche
    {
        return $this->fiche;
    }

    public function setFiche(?Fiche $fiche): static
    {
        $this->fiche = $fiche;
        return $this;
    }

    public function getOldDate(): ?\DateTimeInterface
    {
        return $this->oldDate;
    }

    public function setOldDate(\DateTimeInterface $oldDate): static
    {
        $this->oldDate = $oldDate;
        return $this;
    }

    public function getNewDate(): ?\DateTimeInterface
    {
        return $this->newDate;
    }

    public function setNewDate(\DateTimeInterface $newDate): static
    {
        $this->newDate = $newDate;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        return $this;
    }
}
