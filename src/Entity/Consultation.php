<?php

namespace App\Entity;

use App\Repository\ConsultationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomMedecin = null;

    #[ORM\Column(length: 255)]
    private ?string $nomPatient = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateConsultation = null;

    #[ORM\OneToMany(mappedBy: 'consultation', targetEntity: Fiche::class, orphanRemoval: true)]
    private Collection $fiches;

    #[ORM\OneToMany(mappedBy: 'consultation', targetEntity: Message::class, orphanRemoval: true)]
    private Collection $messages;

    public function __construct()
    {
        $this->fiches = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomMedecin(): ?string
    {
        return $this->nomMedecin;
    }

    public function setNomMedecin(string $nomMedecin): static
    {
        $this->nomMedecin = $nomMedecin;
        return $this;
    }

    public function getNomPatient(): ?string
    {
        return $this->nomPatient;
    }

    public function setNomPatient(string $nomPatient): static
    {
        $this->nomPatient = $nomPatient;
        return $this;
    }

    public function getDateConsultation(): ?\DateTimeInterface
    {
        return $this->dateConsultation;
    }

    public function setDateConsultation(\DateTimeInterface $dateConsultation): static
    {
        $this->dateConsultation = $dateConsultation;
        return $this;
    }

    /**
     * @return Collection<int, Fiche>
     */
    public function getFiches(): Collection
    {
        return $this->fiches;
    }

    public function addFiche(Fiche $fiche): static
    {
        if (!$this->fiches->contains($fiche)) {
            $this->fiches->add($fiche);
            $fiche->setConsultation($this);
        }

        return $this;
    }

    public function removeFiche(Fiche $fiche): static
    {
        if ($this->fiches->removeElement($fiche)) {
            // set the owning side to null (unless already changed)
            if ($fiche->getConsultation() === $this) {
                $fiche->setConsultation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConsultation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getConsultation() === $this) {
                $message->setConsultation(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nomPatient . ' - ' . $this->dateConsultation->format('Y-m-d H:i');
    }
}
