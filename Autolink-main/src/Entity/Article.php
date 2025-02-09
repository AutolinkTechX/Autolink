<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datecreation = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column]
    private ?int $quantitestock = null;

    /**
     * @var Collection<int, ListArticle>
     */
    #[ORM\OneToMany(targetEntity: ListArticle::class, mappedBy: 'article')]
    private Collection $listArticles;

    /**
     * @var Collection<int, Favorie>
     */
    #[ORM\ManyToMany(targetEntity: Favorie::class, mappedBy: 'article')]
    private Collection $favories;

    public function __construct()
    {
        $this->listArticles = new ArrayCollection();
        $this->favories = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getDatecreation(): ?\DateTimeInterface
    {
        return $this->datecreation;
    }

    public function setDatecreation(\DateTimeInterface $datecreation): static
    {
        $this->datecreation = $datecreation;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getQuantitestock(): ?int
    {
        return $this->quantitestock;
    }

    public function setQuantitestock(int $quantitestock): static
    {
        $this->quantitestock = $quantitestock;

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
            $listArticle->setArticle($this);
        }

        return $this;
    }

    public function removeListArticle(ListArticle $listArticle): static
    {
        if ($this->listArticles->removeElement($listArticle)) {
            // set the owning side to null (unless already changed)
            if ($listArticle->getArticle() === $this) {
                $listArticle->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favorie>
     */
    public function getFavories(): Collection
    {
        return $this->favories;
    }

    public function addFavory(Favorie $favory): static
    {
        if (!$this->favories->contains($favory)) {
            $this->favories->add($favory);
            $favory->addArticle($this);
        }

        return $this;
    }

    public function removeFavory(Favorie $favory): static
    {
        if ($this->favories->removeElement($favory)) {
            $favory->removeArticle($this);
        }

        return $this;
    }
}
