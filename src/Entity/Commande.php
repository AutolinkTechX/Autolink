<?php
namespace App\Entity;

use App\Repository\CommandeRepository;
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
    private ?string $modePaiement = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    // Nouveau champ pour stocker les identifiants des articles
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $articleIds = null;

    public function __construct()
    {
        // Initialisation de articleIds comme tableau vide
        $this->articleIds = [];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getModePaiement(): ?string
    {
        return $this->modePaiement;
    }

    public function setModePaiement(string $modePaiement): self
    {
        $this->modePaiement = $modePaiement;
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

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getArticleIds(): ?array
    {
        return $this->articleIds;
    }

    public function setArticleIds(?array $articleIds): static
    {
        $this->articleIds = $articleIds;
        return $this;
    }

    public function addArticleId(int $articleId): static
    {
        if (!in_array($articleId, $this->articleIds, true)) {
            $this->articleIds[] = $articleId;
        }
        return $this;
    }

    public function removeArticleId(int $articleId): static
    {
        if (($key = array_search($articleId, $this->articleIds, true)) !== false) {
            unset($this->articleIds[$key]);
        }
        return $this;
    }
}
