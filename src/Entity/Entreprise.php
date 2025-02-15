<?php
namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
#[ORM\Table(name: 'entreprise')]
final class Entreprise implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $company_name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $phone = null;

    #[ORM\Embedded(class: Address::class)]
    private Address $address;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $tax_code = null;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'entreprises')]
    private ?Role $role = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $supplier = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $field = null;

    /**
     * @var Collection<int, MaterielRecyclable>
     */
    #[ORM\OneToMany(targetEntity: MaterielRecyclable::class, mappedBy: 'Entreprise')]
    private Collection $materielRecyclables;

    /**
     * @var Collection<int, AccordRecyclage>
     */
    #[ORM\OneToMany(targetEntity: AccordRecyclage::class, mappedBy: 'Entreprise')]
    private Collection $accordRecyclages;

    public function __construct()
    {
        $this->materielRecyclables = new ArrayCollection();
        $this->accordRecyclages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    public function setCompanyName(string $company_name): static
    {
        $this->company_name = $company_name;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getTaxCode(): ?string
    {
        return $this->tax_code;
    }

    public function setTaxCode(string $tax_code): static
    {
        $this->tax_code = $tax_code;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function eraseCredentials()
    {
        // No credentials to erase
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function isSupplier(): ?bool
    {
        return $this->supplier;
    }

    public function setSupplier(bool $supplier): static
    {
        $this->supplier = $supplier;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->getRole() ? [$this->getRole()->getName()] : [];
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return Collection<int, MaterielRecyclable>
     */
    public function getMaterielRecyclables(): Collection
    {
        return $this->materielRecyclables;
    }

    public function addMaterielRecyclable(MaterielRecyclable $materielRecyclable): static
    {
        if (!$this->materielRecyclables->contains($materielRecyclable)) {
            $this->materielRecyclables->add($materielRecyclable);
            $materielRecyclable->setEntreprise($this);
        }

        return $this;
    }

    public function removeMaterielRecyclable(MaterielRecyclable $materielRecyclable): static
    {
        if ($this->materielRecyclables->removeElement($materielRecyclable)) {
            // set the owning side to null (unless already changed)
            if ($materielRecyclable->getEntreprise() === $this) {
                $materielRecyclable->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AccordRecyclage>
     */
    public function getAccordRecyclages(): Collection
    {
        return $this->accordRecyclages;
    }

    public function addAccordRecyclage(AccordRecyclage $accordRecyclage): static
    {
        if (!$this->accordRecyclages->contains($accordRecyclage)) {
            $this->accordRecyclages->add($accordRecyclage);
            $accordRecyclage->setEntreprise($this);
        }

        return $this;
    }

    public function removeAccordRecyclage(AccordRecyclage $accordRecyclage): static
    {
        if ($this->accordRecyclages->removeElement($accordRecyclage)) {
            // set the owning side to null (unless already changed)
            if ($accordRecyclage->getEntreprise() === $this) {
                $accordRecyclage->setEntreprise(null);
            }
        }

        return $this;
    }
}