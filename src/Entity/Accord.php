<?php

namespace App\Entity;

use App\Repository\AccordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccordRepository::class)]
class Accord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $statudemande = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datecreation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datereception = null;

    #[ORM\Column(length: 255)]
    private ?string $quantity = null;

    #[ORM\ManyToOne(targetEntity: MaterielRecyclable::class, inversedBy: 'accords')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MaterielRecyclable $materielRecyclable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $output = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatudemande(): ?string
    {
        return $this->statudemande;
    }

    public function setStatudemande(string $statudemande): static
    {
        $this->statudemande = $statudemande;
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

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getMaterielRecyclable(): ?MaterielRecyclable
    {
        return $this->materielRecyclable;
    }

    public function setMaterielRecyclable(?MaterielRecyclable $materielRecyclable): static
    {
        $this->materielRecyclable = $materielRecyclable;
        return $this;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): static
    {
        $this->output = $output;
        return $this;
    }
}
