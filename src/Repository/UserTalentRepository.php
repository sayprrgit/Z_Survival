<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Talent;
use App\Entity\User;
use App\Entity\UserTalent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class UserTalentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTalent::class);
    }

    public function owns(User $user, Talent $talent): bool
    {
        return $this->count(['user' => $user, 'talent' => $talent]) > 0;
    }

    /** @return list<UserTalent> */
    public function findAllForUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['acquiredAt' => 'ASC']);
    }

    /** @return list<string> */
    public function findCodesForUser(User $user): array
    {
        $codes = $this->createQueryBuilder('userTalent')
            ->select('talent.code AS code')
            ->join('userTalent.talent', 'talent')
            ->andWhere('userTalent.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getScalarResult();

        return array_map(static fn (array $row): string => (string) $row['code'], $codes);
    }
}
