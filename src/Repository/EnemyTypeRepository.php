<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EnemyType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class EnemyTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnemyType::class);
    }

    /** @return list<EnemyType> */
    public function findAllForShowcase(): array
    {
        return $this->createQueryBuilder('enemy')
            ->orderBy('enemy.isBoss', 'ASC')
            ->addOrderBy('enemy.availableFromSeconds', 'ASC')
            ->addOrderBy('enemy.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
