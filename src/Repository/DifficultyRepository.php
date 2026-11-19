<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Difficulty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DifficultyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Difficulty::class);
    }

    /** @return list<Difficulty> */
    public function findAllForGame(): array
    {
        return $this->createQueryBuilder('difficulty')
            ->orderBy('difficulty.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
