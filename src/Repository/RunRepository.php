<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Run;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\Persistence\ManagerRegistry;

final class RunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Run::class);
    }

    public function findOwnedForUpdate(int $id, User $user): ?Run
    {
        $query = $this->createQueryBuilder('run')
            ->andWhere('run.id = :id')
            ->andWhere('run.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery();

        if (!$this->getEntityManager()->getConnection()->getDatabasePlatform() instanceof SQLitePlatform) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getOneOrNullResult();
    }

    /** @return list<Run> */
    public function findPageForUser(User $user, int $page, int $limit): array
    {
        return $this->createQueryBuilder('run')
            ->andWhere('run.user = :user')
            ->setParameter('user', $user)
            ->orderBy('run.startedAt', 'DESC')
            ->addOrderBy('run.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countForUser(User $user): int
    {
        return $this->count(['user' => $user]);
    }
}
