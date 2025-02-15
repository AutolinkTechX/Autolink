<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
final class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $last_name = null;

    #[ORM\Column]
    private ?int $phone = null;

    #[ORM\Column(length: 255, unique: true, options: ["message" => "This email is already in use."])]
    #[Assert\NotBlank(message:"Email is required")]
    #[Assert\Email(message:"The Email {{ value }} is not a vaild email ")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 6, max: 255, minMessage: "Your password must contain at least {{ limit }} characters.", maxMessage: "Your password must be less than {{ limit }} characters.")]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $role = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Embedded(class: Address::class)]
    private Address $address;

    /**
     * @var Collection<int, MaterielRecyclable>
     */
    #[ORM\OneToMany(targetEntity: MaterielRecyclable::class, mappedBy: 'User')]
    private Collection $materielRecyclables;

    /**
     * @var Collection<int, AccordRecyclage>
     */
    #[ORM\OneToMany(targetEntity: AccordRecyclage::class, mappedBy: 'User')]
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
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

    // UserInterface methods
    public function getRoles(): array
    {
        return $this->getRole() ? [$this->getRole()->getName()] : [];
    }

    public function eraseCredentials()
    {
        // No credentials to erase
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;

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
            $materielRecyclable->setUser($this);
        }

        return $this;
    }

    public function removeMaterielRecyclable(MaterielRecyclable $materielRecyclable): static
    {
        if ($this->materielRecyclables->removeElement($materielRecyclable)) {
            // set the owning side to null (unless already changed)
            if ($materielRecyclable->getUser() === $this) {
                $materielRecyclable->setUser(null);
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
            $accordRecyclage->setUser($this);
        }

        return $this;
    }

    public function removeAccordRecyclage(AccordRecyclage $accordRecyclage): static
    {
        if ($this->accordRecyclages->removeElement($accordRecyclage)) {
            // set the owning side to null (unless already changed)
            if ($accordRecyclage->getUser() === $this) {
                $accordRecyclage->setUser(null);
            }
        }

        return $this;
    }

}