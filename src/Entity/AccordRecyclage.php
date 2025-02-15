<?php

namespace App\Entity;

use App\Repository\AccordRecyclageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccordRecyclageRepository::class)]
class AccordRecyclage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datecreation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datereception = null;

    #[ORM\Column(length: 255)]
    private ?string $statutdemande = null;

    #[ORM\Column(length: 255)]
    private ?string $output = null;

    #[ORM\ManyToOne(inversedBy: 'accordRecyclages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    #[ORM\ManyToOne(inversedBy: 'accordRecyclages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $Entreprise = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

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

    public function getDatereception(): ?\DateTimeInterface
    {
        return $this->datereception;
    }

    public function setDatereception(\DateTimeInterface $datereception): static
    {
        $this->datereception = $datereception;

        return $this;
    }

    public function getStatutdemande(): ?string
    {
        return $this->statutdemande;
    }

    public function setStatutdemande(string $statutdemande): static
    {
        $this->statutdemande = $statutdemande;

        return $this;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(string $output): static
    {
        $this->output = $output;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->Entreprise;
    }

    public function setEntreprise(?Entreprise $Entreprise): static
    {
        $this->Entreprise = $Entreprise;

        return $this;
    }
}
