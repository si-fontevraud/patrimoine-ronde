<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ReportPriority;
use App\Repository\PriorityThresholdRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: PriorityThresholdRepository::class)]
#[ORM\Table(name: 'priority_threshold')]
class PriorityThreshold
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'smallint')]
    private int $minScore = 1;

    #[ORM\Column(type: 'smallint')]
    private int $maxScore = 15;

    #[ORM\Column(type: 'string', enumType: ReportPriority::class)]
    private ReportPriority $priority = ReportPriority::MEDIUM;

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);

        return $this;
    }

    public function getMinScore(): int
    {
        return $this->minScore;
    }

    public function setMinScore(int $minScore): self
    {
        $this->minScore = $minScore;

        return $this;
    }

    public function getMaxScore(): int
    {
        return $this->maxScore;
    }

    public function setMaxScore(int $maxScore): self
    {
        $this->maxScore = $maxScore;

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

    public function __toString(): string
    {
        return sprintf('%s (%d-%d)', $this->name ?? '', $this->minScore, $this->maxScore);
    }
}

