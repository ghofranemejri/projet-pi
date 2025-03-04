<?php

namespace App\Entity;

use App\Repository\FormPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\BadWordFilter;

#[ORM\Entity(repositoryClass: FormPostRepository::class)]
class FormPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(min: 3, max: 50, minMessage: "Le nom doit avoir au moins 3 caractères.")]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "La date est requise.")]
    #[Assert\Type("\DateTimeInterface", message: "Le format de la date est invalide.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(max: 500, maxMessage: "La description ne peut pas dépasser 500 caractères.")]
    private ?string $description = null;

    #[ORM\Column(type: 'integer')]
private int $likes = 0;

#[ORM\Column(type: 'integer')]
private int $dislikes = 0;

    /**
     * @var Collection<int, Reponse>
     */
    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: 'nom', cascade: ['remove'], orphanRemoval: true)]
    private Collection $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
       
        $this->date = new \DateTime(); // Met la date actuelle par défaut
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setNom($this);
        }
        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getNom() === $this) {
                $reponse->setNom(null);
            }
        }
        return $this;
    }

    public function getLikes(): int
{
    return $this->likes;
}

public function getDislikes(): int
{
    return $this->dislikes;
}

public function addLike(): void
{
    $this->likes++;
}

public function addDislike(): void
{
    $this->dislikes++;
}

}
