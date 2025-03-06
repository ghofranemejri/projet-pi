<?php

// src/Entity/Conversation.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ConversationRepository;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user1;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user2;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation')]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser1(): User
    {
        return $this->user1;
    }

    public function setUser1(User $user1): static
    {
        $this->user1 = $user1;
        return $this;
    }

    public function getUser2(): User
    {
        return $this->user2;
    }

    public function setUser2(User $user2): static
    {
        $this->user2 = $user2;
        return $this;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }
}
