<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date du rendez-vous ne peut pas être vide.")]
    #[Assert\Type("\DateTimeInterface", message: "La date doit être un format valide.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le statut du rendez-vous ne peut pas être vide.")]
    #[Assert\Length(
        min: 3, 
        max: 50, 
        minMessage: "Le statut doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le statut ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $statut = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le motif du rendez-vous ne peut pas être vide.")]
    #[Assert\Length(
        min: 10, 
        max: 500, 
        minMessage: "Le motif doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le motif ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $motif = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVouses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Le patient doit être sélectionné.")]
    private ?User $patient = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVouses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Le médecin doit être sélectionné.")]
    private ?User $medecin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de création ne peut pas être vide.")]
    #[Assert\Type("\DateTimeInterface", message: "La date de création doit être un format valide.")]
    private ?\DateTimeInterface $date_creation = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): static
    {
        $this->motif = $motif;

        return $this;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getMedecin(): ?User
    {
        return $this->medecin;
    }

    public function setMedecin(?User $medecin): static
    {
        $this->medecin = $medecin;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    // Get patient email
    public function getPatientEmail(): ?string
    {
        return $this->patient ? $this->patient->getEmail() : null;
    }

    // Get medecin email
    public function getMedecinEmail(): ?string
    {
        return $this->medecin ? $this->medecin->getEmail() : null;
    }
}
