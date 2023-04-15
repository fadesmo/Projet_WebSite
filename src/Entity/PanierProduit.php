<?php

namespace App\Entity;

use App\Repository\PanierProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\DBAL\Types\Types;


#[ORM\Table(name: 'i23_paniers_produits')]
#[ORM\UniqueConstraint(columns: ['id_panier', 'id_produit'])]
#[ORM\Entity(repositoryClass: PanierProduitRepository::class)]
#[UniqueEntity( fields: ['panier', 'Produit'] , message: 'cette relation est déjà présente', errorPath: false)]
class PanierProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\Range(
        minMessage: 'la quantité minimale est {{ limit }}',
        min: 0,
    )]
    private ?int $quantite = null;

    #[ORM\Column(name: 'prix', type: Types::FLOAT)]
    #[Assert\Range(
        minMessage: 'pas de prix négatif',
        min: 0,
    )]
    private ?float $prix = null;

    #[ORM\ManyToOne(targetEntity: Panier::class,inversedBy: 'panierProduits')]
    #[ORM\JoinColumn(name: 'id_panier',nullable: false)]
    #[Assert\NotNull]
    #[Assert\Valid]
    private ?Panier $panier = null;

    #[ORM\ManyToOne(targetEntity: Produit::class,inversedBy: 'panierProduits')]
    #[ORM\JoinColumn(name: 'id_produit',nullable: false)]
    #[Assert\NotNull]
    #[Assert\Valid]
    private ?Produit $produit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): self
    {
        $this->panier = $panier;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }
}
