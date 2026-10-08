<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ReportInterventionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ReportInterventionRepository::class)]
#[ORM\Table(name: 'report_intervention')]
#[ORM\Index(columns: ['performed_at'])]
class ReportIntervention
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(targetEntity: Report::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Report $report = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $actor = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $performedAt = null;

    #[ORM\Column(type: 'text')]
    private ?string $action = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $durationMinutes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $replacedPart = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $result = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attachmentPath = null;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->performedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(Report $report): self
    {
        $this->report = $report;

        return $this;
    }

    public function getActor(): ?User
    {
        return $this->actor;
    }

    public function setActor(?User $actor): self
    {
        $this->actor = $actor;

        return $this;
    }

    public function getPerformedAt(): ?\DateTimeImmutable
    {
        return $this->performedAt;
    }

    public function setPerformedAt(\DateTimeImmutable $performedAt): self
    {
        $this->performedAt = $performedAt;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = trim($action);

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): self
    {
        $this->durationMinutes = $durationMinutes;

        return $this;
    }

    public function getReplacedPart(): ?string
    {
        return $this->replacedPart;
    }

    public function setReplacedPart(?string $replacedPart): self
    {
        $this->replacedPart = $replacedPart !== null ? trim($replacedPart) : null;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): self
    {
        $this->result = $result !== null ? trim($result) : null;

        return $this;
    }

    public function getAttachmentPath(): ?string
    {
        return $this->attachmentPath;
    }

    public function setAttachmentPath(?string $attachmentPath): self
    {
        $this->attachmentPath = $attachmentPath !== null ? trim($attachmentPath) : null;

        return $this;
    }
}

