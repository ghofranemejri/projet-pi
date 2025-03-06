<?php

namespace App\Entity;

use App\Repository\FicheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FicheRepository::class)]
class Fiche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomPatient = null;

    #[ORM\Column(length: 255)]
    private ?string $nomMedecin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motif = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $diagnostic = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $traitement = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'fiche', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviews;

    #[ORM\ManyToOne(inversedBy: 'fiches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Consultation $consultation = null;

    #[ORM\OneToMany(mappedBy: 'fiche', targetEntity: DateChangeNotification::class, orphanRemoval: true)]
    private Collection $dateChangeNotifications;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->dateChangeNotifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNomMedecin(): ?string
    {
        return $this->nomMedecin;
    }

    public function setNomMedecin(string $nomMedecin): static
    {
        $this->nomMedecin = $nomMedecin;
        return $this;
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

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): static
    {
        $this->motif = $motif;
        return $this;
    }

    public function getDiagnostic(): ?string
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(?string $diagnostic): static
    {
        $this->diagnostic = $diagnostic;
        return $this;
    }

    public function getTraitement(): ?string
    {
        return $this->traitement;
    }

    public function setTraitement(?string $traitement): static
    {
        $this->traitement = $traitement;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getConsultation(): ?Consultation
    {
        return $this->consultation;
    }

    public function setConsultation(?Consultation $consultation): static
    {
        $this->consultation = $consultation;
        return $this;
    }

    public function getConsultationDate(): ?\DateTimeInterface
    {
        return $this->consultation ? $this->consultation->getDateConsultation() : null;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setFiche($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getFiche() === $this) {
                $review->setFiche(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DateChangeNotification>
     */
    public function getDateChangeNotifications(): Collection
    {
        return $this->dateChangeNotifications;
    }

    public function addDateChangeNotification(DateChangeNotification $notification): static
    {
        if (!$this->dateChangeNotifications->contains($notification)) {
            $this->dateChangeNotifications->add($notification);
            $notification->setFiche($this);
        }

        return $this;
    }

    public function removeDateChangeNotification(DateChangeNotification $notification): static
    {
        if ($this->dateChangeNotifications->removeElement($notification)) {
            if ($notification->getFiche() === $this) {
                $notification->setFiche(null);
            }
        }

        return $this;
    }

    public function getAverageRating(): float
    {
        if ($this->reviews->isEmpty()) {
            return 0;
        }

        $total = 0;
        foreach ($this->reviews as $review) {
            $total += $review->getRating();
        }

        return round($total / $this->reviews->count(), 1);
    }
}
