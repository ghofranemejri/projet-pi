<?php

namespace App\Entity;

use App\Repository\ShareRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShareRepository::class)]
class Share
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?FormPost $formPost = null;

    #[ORM\ManyToOne(inversedBy: 'shares')]
    private ?user $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $DateTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormPost(): ?FormPost
    {
        return $this->formPost;
    }

    public function setFormPost(?FormPost $formPost): static
    {
        $this->formPost = $formPost;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->DateTime;
    }

    public function setDateTime(\DateTimeInterface $DateTime): static
    {
        $this->DateTime = $DateTime;

        return $this;
    }
}
