<?php

namespace App\Entity;

use App\Repository\FavorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavorieRepository::class)]
class Favorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_article = null;

    //#[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'favories')]
    //#[ORM\JoinColumn(nullable: false)]
    //private ?Client $client = null;

    //#[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'favories')]
   // #[ORM\JoinColumn(nullable: false)]
    //private ?Article $article = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getId_article(): ?int
    {
        return $this->id_article;
    }

    public function setId_article(int $id): static
    {
        $this->id_article = $id;

        return $this;
    }

   /* public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;
        return $this;
    }*/
}
