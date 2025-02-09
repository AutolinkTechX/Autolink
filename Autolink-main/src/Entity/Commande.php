<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    /**
     * @var Collection<int, ListArticle>
     */
    #[ORM\OneToMany(targetEntity: ListArticle::class, mappedBy: 'Commande')]
    private Collection $listArticles;

    public function __construct()
    {
        $this->listArticles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, ListArticle>
     */
    public function getListArticles(): Collection
    {
        return $this->listArticles;
    }

    public function addListArticle(ListArticle $listArticle): static
    {
        if (!$this->listArticles->contains($listArticle)) {
            $this->listArticles->add($listArticle);
            $listArticle->setCommande($this);
        }

        return $this;
    }

    public function removeListArticle(ListArticle $listArticle): static
    {
        if ($this->listArticles->removeElement($listArticle)) {
            // set the owning side to null (unless already changed)
            if ($listArticle->getCommande() === $this) {
                $listArticle->setCommande(null);
            }
        }

        return $this;
    }
}
