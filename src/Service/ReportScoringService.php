<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PriorityThreshold;
use App\Entity\Report;
use App\Enum\ReportPriority;
use App\Repository\PriorityThresholdRepository;

final class ReportScoringService
{
    public function __construct(
        private readonly PriorityThresholdRepository $priorityThresholdRepository,
    ) {
    }

    public function applyPriorityFromScore(Report $report): void
    {
        $score = $report->getTotalScore();
        $threshold = $this->resolveThreshold($score);

        if ($threshold instanceof PriorityThreshold) {
            $report->setPriority($threshold->getPriority());

            return;
        }

        $report->setPriority($this->fallbackPriority($score));
    }

    private function resolveThreshold(int $score): ?PriorityThreshold
    {
        $thresholds = $this->priorityThresholdRepository->findBy([], ['minScore' => 'ASC']);

        foreach ($thresholds as $threshold) {
            if ($score >= $threshold->getMinScore() && $score <= $threshold->getMaxScore()) {
                return $threshold;
            }
        }

        return null;
    }

    private function fallbackPriority(int $score): ReportPriority
    {
        return match (true) {
            $score >= 13 => ReportPriority::CRITICAL,
            $score >= 10 => ReportPriority::HIGH,
            $score >= 7 => ReportPriority::MEDIUM,
            default => ReportPriority::LOW,
        };
    }
}

