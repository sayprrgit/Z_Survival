<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Character;
use App\Entity\User;
use App\Entity\UserCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class UserCharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCharacter::class);
    }

    public function owns(User $user, Character $character): bool
    {
        return $this->count(['user' => $user, 'character' => $character]) > 0;
    }

    /** @return list<UserCharacter> */
    public function findAllForUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['acquiredAt' => 'ASC']);
    }
}
