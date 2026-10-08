<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ControlPointRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ControlPointRepository::class)]
#[ORM\Table(name: 'control_point')]
#[ORM\Index(columns: ['sort_order'])]
class ControlPoint
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $reference = null;

    #[ORM\ManyToOne(targetEntity: Zone::class, inversedBy: 'controlPoints')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Zone $zone = null;

    #[ORM\ManyToOne(targetEntity: Equipment::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Equipment $equipment = null;

    #[ORM\Column(length: 150)]
    private ?string $label = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $instruction = null;

    #[ORM\Column(length: 50)]
    private string $frequency = 'daily';

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(type: 'smallint')]
    private int $defaultCriticality = 3;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'controlPoint', targetEntity: PatrolResult::class, cascade: ['persist', 'remove'])]
    private Collection $results;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->reference = 'PC-'.strtoupper(substr((string) $this->id, 0, 8));
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->results = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = strtoupper(trim($reference));

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(Zone $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(?Equipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = trim($label);

        return $this;
    }

    public function getInstruction(): ?string
    {
        return $this->instruction;
    }

    public function setInstruction(?string $instruction): self
    {
        $this->instruction = $instruction !== null ? trim($instruction) : null;

        return $this;
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): self
    {
        $this->frequency = trim($frequency);

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getDefaultCriticality(): int
    {
        return $this->defaultCriticality;
    }

    public function setDefaultCriticality(int $defaultCriticality): self
    {
        $this->defaultCriticality = max(1, min(5, $defaultCriticality));

        return $this;
    }

    public function isIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->reference ?? '', $this->label ?? '');
    }
}

