<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ReportPriority;
use App\Enum\ReportSource;
use App\Enum\ReportStatus;
use App\Repository\ReportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ORM\Table(name: 'report')]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['priority'])]
#[ORM\Index(columns: ['created_at'])]
class Report
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $reference = null;

    #[ORM\ManyToOne(targetEntity: Zone::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Zone $zone = null;

    #[ORM\ManyToOne(targetEntity: Equipment::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Equipment $equipment = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $assignedTo = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $servicePilot = null;

    #[ORM\Column(type: 'json')]
    private array $supportServices = [];

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'string', enumType: ReportPriority::class)]
    private ReportPriority $priority = ReportPriority::MEDIUM;

    #[ORM\Column(type: 'string', enumType: ReportStatus::class)]
    private ReportStatus $status = ReportStatus::NEW;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $waitingReason = null;

    #[ORM\Column(type: 'string', enumType: ReportSource::class)]
    private ReportSource $source = ReportSource::WEB;

    #[ORM\Column(type: 'smallint')]
    private int $impactScore = 1;

    #[ORM\Column(type: 'smallint')]
    private int $urgencyScore = 1;

    #[ORM\Column(type: 'smallint')]
    private int $aggravationScore = 1;

    #[ORM\Column(type: 'smallint')]
    private int $totalScore = 3;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $observedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $resolvedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $closedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dueAt = null;

    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ReportPhoto::class, cascade: ['persist', 'remove'])]
    private Collection $photos;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->observedAt = $this->createdAt;
        $this->reference = 'SIG-'.strtoupper(substr((string) $this->id, 0, 8));
        $this->photos = new ArrayCollection();
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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): self
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    public function getServicePilot(): ?string
    {
        return $this->servicePilot;
    }

    public function setServicePilot(?string $servicePilot): self
    {
        $this->servicePilot = $servicePilot !== null ? trim($servicePilot) : null;

        return $this;
    }

    public function getSupportServices(): array
    {
        return $this->supportServices;
    }

    public function setSupportServices(array $supportServices): self
    {
        $this->supportServices = array_values(array_unique(array_filter(array_map(
            static fn (mixed $service): string => trim((string) $service),
            $supportServices
        ))));

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = trim($title);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = trim($description);

        return $this;
    }

    public function getPriority(): ReportPriority
    {
        return $this->priority;
    }

    public function setPriority(ReportPriority $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): ReportStatus
    {
        return $this->status;
    }

    public function setStatus(ReportStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getWaitingReason(): ?string
    {
        return $this->waitingReason;
    }

    public function setWaitingReason(?string $waitingReason): self
    {
        $this->waitingReason = $waitingReason !== null ? trim($waitingReason) : null;

        return $this;
    }

    public function getSource(): ReportSource
    {
        return $this->source;
    }

    public function setSource(ReportSource $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getImpactScore(): int
    {
        return $this->impactScore;
    }

    public function setImpactScore(int $impactScore): self
    {
        $this->impactScore = $this->normalizeScore($impactScore);
        $this->refreshTotalScore();

        return $this;
    }

    public function getUrgencyScore(): int
    {
        return $this->urgencyScore;
    }

    public function setUrgencyScore(int $urgencyScore): self
    {
        $this->urgencyScore = $this->normalizeScore($urgencyScore);
        $this->refreshTotalScore();

        return $this;
    }

    public function getAggravationScore(): int
    {
        return $this->aggravationScore;
    }

    public function setAggravationScore(int $aggravationScore): self
    {
        $this->aggravationScore = $this->normalizeScore($aggravationScore);
        $this->refreshTotalScore();

        return $this;
    }

    public function getTotalScore(): int
    {
        return $this->totalScore;
    }

    public function getObservedAt(): ?\DateTimeImmutable
    {
        return $this->observedAt;
    }

    public function setObservedAt(\DateTimeImmutable $observedAt): self
    {
        $this->observedAt = $observedAt;

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

    public function getResolvedAt(): ?\DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?\DateTimeImmutable $resolvedAt): self
    {
        $this->resolvedAt = $resolvedAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeImmutable $closedAt): self
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getDueAt(): ?\DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function setDueAt(?\DateTimeImmutable $dueAt): self
    {
        $this->dueAt = $dueAt;

        return $this;
    }

    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(ReportPhoto $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setReport($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->reference ?? 'SIG', $this->title ?? '');
    }

    private function refreshTotalScore(): void
    {
        $this->totalScore = $this->impactScore + $this->urgencyScore + $this->aggravationScore;
    }

    private function normalizeScore(int $score): int
    {
        return max(1, min(5, $score));
    }
}
