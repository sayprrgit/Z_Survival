<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\CharacterCode;
use App\Entity\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    /** @return list<Character> */
    public function findAllForSelection(): array
    {
        return $this->createQueryBuilder('character')
            ->addSelect('weapon')
            ->innerJoin('character.startingWeapon', 'weapon')
            ->orderBy('character.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByCodeValue(string $code): ?Character
    {
        $characterCode = CharacterCode::tryFrom($code);

        return $characterCode === null ? null : $this->findOneBy(['code' => $characterCode]);
    }
}
