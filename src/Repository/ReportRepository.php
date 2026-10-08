<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report;
use App\Enum\ReportStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    public function findOpenReports(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status != :closedStatus')
            ->setParameter('closedStatus', ReportStatus::CLOSED)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
