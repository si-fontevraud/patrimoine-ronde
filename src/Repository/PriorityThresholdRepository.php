<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PriorityThreshold;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PriorityThresholdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriorityThreshold::class);
    }
}

