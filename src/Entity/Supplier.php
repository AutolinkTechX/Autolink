<?php

namespace App\Entity;

use App\Repository\SupplierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SupplierRepository::class)]
class Supplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'entreprise est obligatoire.")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email n'est pas valide.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/",
        message: "L'email doit être au format valide (ex: exemple@domaine.com)."
    )]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le numéro de téléphone est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^\+?[0-9]{8,15}$/",
        message: "Le numéro de téléphone doit contenir entre 8 et 15 chiffres."
    )]
    private ?int $phone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le code fiscal est obligatoire.")]
    #[Assert\Length(
        min: 8,
        max: 15,
        minMessage: "Le code fiscal doit contenir au moins 8 caractères.",
        maxMessage: "Le code fiscal ne doit pas dépasser 15 caractères."
    )]
    private ?string $taxcode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Veuillez sélectionner un type de recyclage.")]
    private ?string $typeRecyclage = null;

    /**
     * @var Collection<int, SupplierContract>
     */
    #[ORM\OneToMany(targetEntity: SupplierContract::class, mappedBy: 'supplier')]
    private Collection $startDate;

    /**
     * @var Collection<int, SupplierContract>
     */
    #[ORM\OneToMany(targetEntity: SupplierContract::class, mappedBy: 'supplier')]
    private Collection $supplierContracts;

    /**
     * @var Collection<int, RecyclableMaterial>
     */
    #[ORM\OneToMany(targetEntity: RecyclableMaterial::class, mappedBy: 'supplier')]
    private Collection $recyclableMaterials;

    public function __construct()
    {
        $this->startDate = new ArrayCollection();
        $this->supplierContracts = new ArrayCollection();
        $this->recyclableMaterials = new ArrayCollection();
    }

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function setPhone(int $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getTaxcode(): ?string
    {
        return $this->taxcode;
    }

    public function setTaxcode(string $taxcode): static
    {
        $this->taxcode = $taxcode;
        return $this;
    }

    public function getTypeRecyclage(): ?string
    {
        return $this->typeRecyclage;
    }

    public function setTypeRecyclage(?string $typeRecyclage): static
    {
        $this->typeRecyclage = $typeRecyclage;
        return $this;
    }

    /**
     * @return Collection<int, SupplierContract>
     */
    public function getStartDate(): Collection
    {
        return $this->startDate;
    }

    public function addStartDate(SupplierContract $startDate): static
    {
        if (!$this->startDate->contains($startDate)) {
            $this->startDate->add($startDate);
            $startDate->setSupplier($this);
        }

        return $this;
    }

    public function removeStartDate(SupplierContract $startDate): static
    {
        if ($this->startDate->removeElement($startDate)) {
            // set the owning side to null (unless already changed)
            if ($startDate->getSupplier() === $this) {
                $startDate->setSupplier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SupplierContract>
     */
    public function getSupplierContracts(): Collection
    {
        return $this->supplierContracts;
    }

    public function addSupplierContract(SupplierContract $supplierContract): static
    {
        if (!$this->supplierContracts->contains($supplierContract)) {
            $this->supplierContracts->add($supplierContract);
            $supplierContract->setSupplier($this);
        }

        return $this;
    }

    public function removeSupplierContract(SupplierContract $supplierContract): static
    {
        if ($this->supplierContracts->removeElement($supplierContract)) {
            // set the owning side to null (unless already changed)
            if ($supplierContract->getSupplier() === $this) {
                $supplierContract->setSupplier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RecyclableMaterial>
     */
    public function getRecyclableMaterials(): Collection
    {
        return $this->recyclableMaterials;
    }

    public function addRecyclableMaterial(RecyclableMaterial $recyclableMaterial): static
    {
        if (!$this->recyclableMaterials->contains($recyclableMaterial)) {
            $this->recyclableMaterials->add($recyclableMaterial);
            $recyclableMaterial->setSupplier($this);
        }

        return $this;
    }

    public function removeRecyclableMaterial(RecyclableMaterial $recyclableMaterial): static
    {
        if ($this->recyclableMaterials->removeElement($recyclableMaterial)) {
            // set the owning side to null (unless already changed)
            if ($recyclableMaterial->getSupplier() === $this) {
                $recyclableMaterial->setSupplier(null);
            }
        }

        return $this;
    }
}
