<?php

namespace App\Entity;

use App\Repository\FavoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoRepository::class)]
class Favo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Client>
     */
    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: 'favo')]
    private Collection $ic_client;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\ManyToMany(targetEntity: Article::class)]
    private Collection $id_article;

    public function __construct()
    {
        $this->ic_client = new ArrayCollection();
        $this->id_article = new ArrayCollection();
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

    /**
     * @return Collection<int, Client>
     */
    public function getIcClient(): Collection
    {
        return $this->ic_client;
    }

    public function addIcClient(Client $icClient): static
    {
        if (!$this->ic_client->contains($icClient)) {
            $this->ic_client->add($icClient);
            $icClient->setFavo($this);
        }

        return $this;
    }

    public function removeIcClient(Client $icClient): static
    {
        if ($this->ic_client->removeElement($icClient)) {
            // set the owning side to null (unless already changed)
            if ($icClient->getFavo() === $this) {
                $icClient->setFavo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getIdArticle(): Collection
    {
        return $this->id_article;
    }

    public function addIdArticle(Article $idArticle): static
    {
        if (!$this->id_article->contains($idArticle)) {
            $this->id_article->add($idArticle);
        }

        return $this;
    }

    public function removeIdArticle(Article $idArticle): static
    {
        $this->id_article->removeElement($idArticle);

        return $this;
    }
}
