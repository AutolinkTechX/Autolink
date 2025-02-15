<?php
namespace App\Entity;

use App\Repository\MaterielRecyclableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\StatutEnum;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaterielRecyclableRepository::class)]
class MaterielRecyclable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de création est obligatoire")]
    #[Assert\Type(\DateTimeInterface::class, message: "Format de date invalide")]
    private ?\DateTimeInterface $datecreation = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de matériau est obligatoire")]
    #[Assert\Choice(
        choices: ["verre", "plastique", "electronique"],
        message: "Type de matériau non valide"
    )]
    private ?string $typemateriel = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'imagezzz est obligatoire")]
    private ?string $image = null;

    #[ORM\Column(
        type: 'string', // Doctrine stocke l'ENUM en tant que string
        enumType: StatutEnum::class,
        options: ['default' => 'en_attente']
    )]
    

    #[Assert\NotNull(message: "Le statut est obligatoire")]
    private StatutEnum $statut;

    #[ORM\ManyToOne(targetEntity: Entreprise::class, inversedBy: 'materielRecyclables', fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'entreprise est obligatoire")]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'materielRecyclables')]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire")]
    private ?User $user = null;

    /**
     * @var Collection<int, Accord>
     */
    #[ORM\OneToMany(targetEntity: Accord::class, mappedBy: 'MaterielRecyclable')]
    private Collection $output;

    public function __construct()
    {
        $this->output = new ArrayCollection();
    }

    // 🔹 Getters & Setters (inchangés)


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
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

    public function getDatecreation(): ?\DateTimeInterface
    {
        return $this->datecreation;
    }

    public function setDatecreation(?\DateTimeInterface $datecreation): static
    {
        $this->datecreation = $datecreation;
        return $this;
    }

    public function getTypemateriel(): ?string
    {
        return $this->typemateriel;
    }

    public function setTypemateriel(string $typemateriel): static
    {
        $this->typemateriel = $typemateriel;
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

    public function getStatut(): ?StatutEnum
    {
        return $this->statut;
    }

    public function setStatut(StatutEnum $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, Accord>
     */
    public function getOutput(): Collection
    {
        return $this->output;
    }

    public function addOutput(Accord $output): static
    {
        if (!$this->output->contains($output)) {
            $this->output->add($output);
            $output->setMaterielRecyclable($this);
        }

        return $this;
    }

    public function removeOutput(Accord $output): static
    {
        if ($this->output->removeElement($output)) {
            // set the owning side to null (unless already changed)
            if ($output->getMaterielRecyclable() === $this) {
                $output->setMaterielRecyclable(null);
            }
        }

        return $this;
    }
}
