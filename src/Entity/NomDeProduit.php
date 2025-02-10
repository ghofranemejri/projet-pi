<?php

namespace App\Entity;

use App\Repository\NomDeProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NomDeProduitRepository::class)]
class NomDeProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $quantite_de_produit = null;

    #[ORM\Column(length: 255)]
    private ?string $prix_de_produit = null;

    #[ORM\Column(length: 255)]
    private ?string $return = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantiteDeProduit(): ?string
    {
        return $this->quantite_de_produit;
    }

    public function setQuantiteDeProduit(string $quantite_de_produit): static
    {
        $this->quantite_de_produit = $quantite_de_produit;

        return $this;
    }

    public function getPrixDeProduit(): ?string
    {
        return $this->prix_de_produit;
    }

    public function setPrixDeProduit(string $prix_de_produit): static
    {
        $this->prix_de_produit = $prix_de_produit;

        return $this;
    }

    public function getReturn(): ?string
    {
        return $this->return;
    }

    public function setReturn(string $return): static
    {
        $this->return = $return;

        return $this;
    }
}
