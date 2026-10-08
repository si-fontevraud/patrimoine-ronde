<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PatrolCheckResult;
use App\Repository\PatrolResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: PatrolResultRepository::class)]
#[ORM\Table(name: 'patrol_result')]
#[ORM\Index(columns: ['checked_at'])]
class PatrolResult
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(targetEntity: PatrolRound::class, inversedBy: 'results')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PatrolRound $patrolRound = null;

    #[ORM\ManyToOne(targetEntity: ControlPoint::class, inversedBy: 'results')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ControlPoint $controlPoint = null;

    #[ORM\ManyToOne(targetEntity: Equipment::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Equipment $equipment = null;

    #[ORM\ManyToOne(targetEntity: Report::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Report $report = null;

    #[ORM\Column(type: 'string', enumType: PatrolCheckResult::class)]
    private PatrolCheckResult $result = PatrolCheckResult::OK;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoPath = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $checkedAt = null;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->checkedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getPatrolRound(): ?PatrolRound
    {
        return $this->patrolRound;
    }

    public function setPatrolRound(PatrolRound $patrolRound): self
    {
        $this->patrolRound = $patrolRound;

        return $this;
    }

    public function getControlPoint(): ?ControlPoint
    {
        return $this->controlPoint;
    }

    public function setControlPoint(ControlPoint $controlPoint): self
    {
        $this->controlPoint = $controlPoint;

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

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): self
    {
        $this->report = $report;

        return $this;
    }

    public function getResult(): PatrolCheckResult
    {
        return $this->result;
    }

    public function setResult(PatrolCheckResult $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment !== null ? trim($comment) : null;

        return $this;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    public function setPhotoPath(?string $photoPath): self
    {
        $this->photoPath = $photoPath !== null ? trim($photoPath) : null;

        return $this;
    }

    public function getCheckedAt(): ?\DateTimeImmutable
    {
        return $this->checkedAt;
    }

    public function setCheckedAt(\DateTimeImmutable $checkedAt): self
    {
        $this->checkedAt = $checkedAt;

        return $this;
    }
}

