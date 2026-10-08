<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PatrolRoundStatus;
use App\Repository\PatrolRoundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: PatrolRoundRepository::class)]
#[ORM\Table(name: 'patrol_round')]
#[ORM\Index(columns: ['started_at'])]
#[ORM\Index(columns: ['status'])]
class PatrolRound
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 32, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 50)]
    private string $type = 'daily';

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $agent = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\Column(type: 'string', enumType: PatrolRoundStatus::class)]
    private PatrolRoundStatus $status = PatrolRoundStatus::DRAFT;

    #[ORM\Column(type: 'integer')]
    private int $totalPoints = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $generalObservation = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'patrolRound', targetEntity: PatrolResult::class, cascade: ['persist', 'remove'])]
    private Collection $results;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->reference = 'RND-'.strtoupper(substr((string) $this->id, 0, 8));
        $this->createdAt = new \DateTimeImmutable();
        $this->startedAt = $this->createdAt;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = trim($type);

        return $this;
    }

    public function getAgent(): ?User
    {
        return $this->agent;
    }

    public function setAgent(User $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getStatus(): PatrolRoundStatus
    {
        return $this->status;
    }

    public function setStatus(PatrolRoundStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalPoints(): int
    {
        return $this->totalPoints;
    }

    public function setTotalPoints(int $totalPoints): self
    {
        $this->totalPoints = max(0, $totalPoints);

        return $this;
    }

    public function getGeneralObservation(): ?string
    {
        return $this->generalObservation;
    }

    public function setGeneralObservation(?string $generalObservation): self
    {
        $this->generalObservation = $generalObservation !== null ? trim($generalObservation) : null;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getResults(): Collection
    {
        return $this->results;
    }

    public function __toString(): string
    {
        return (string) $this->reference;
    }
}

