<?php

declare(strict_types=1);

namespace App\Game\Reward;

use App\DTO\CharacterCatalogItem;
use App\DTO\CharacterUnlockReceipt;
use App\Entity\Character;
use App\Entity\User;
use App\Entity\UserCharacter;
use App\Enum\CharacterCode;
use App\Repository\CharacterRepository;
use App\Repository\UserCharacterRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UnlockService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CharacterRepository $characters,
        private UserCharacterRepository $userCharacters,
    ) {
    }

    public function isUnlocked(User $user, Character $character): bool
    {
        return $character->isAvailableByDefault() || $this->userCharacters->owns($user, $character);
    }

    public function assertUnlocked(User $user, Character $character): void
    {
        if (!$this->isUnlocked($user, $character)) {
            throw new \DomainException('Ce personnage doit être débloqué avant de pouvoir être sélectionné.');
        }
    }

    /** @return list<CharacterCatalogItem> */
    public function catalogFor(User $user): array
    {
        return array_map(fn (Character $character): CharacterCatalogItem => new CharacterCatalogItem(
            $character->getCode()->value,
            $character->getName(),
            $character->getDescription(),
            $character->getPassiveDescription(),
            $character->getBaseHealth(),
            $character->getStartingWeapon()->getCode()->value,
            $this->isUnlocked($user, $character),
            $character->getUnlockCost(),
        ), $this->characters->findBy([], ['code' => 'ASC']));
    }

    public function unlock(User $user, CharacterCode $characterCode): CharacterUnlockReceipt
    {
        if ($user->getId() === null) {
            throw new \DomainException('Un compte enregistré est nécessaire pour débloquer un personnage.');
        }

        return $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($user, $characterCode): CharacterUnlockReceipt {
            $lockMode = $entityManager->getConnection()->getDatabasePlatform() instanceof SQLitePlatform
                ? LockMode::NONE
                : LockMode::PESSIMISTIC_WRITE;
            $lockedUser = $entityManager->find(User::class, $user->getId(), $lockMode);
            if (!$lockedUser instanceof User) {
                throw new \DomainException('Compte introuvable.');
            }

            $character = $this->characters->findOneBy(['code' => $characterCode]);
            if (!$character instanceof Character) {
                throw new \DomainException('Personnage introuvable.');
            }

            if ($this->isUnlocked($lockedUser, $character)) {
                return new CharacterUnlockReceipt($characterCode->value, true, 0, $lockedUser->getScrap());
            }

            $lockedUser->spendScrap($character->getUnlockCost());
            $entityManager->persist(new UserCharacter($lockedUser, $character));
            $entityManager->flush();

            return new CharacterUnlockReceipt($characterCode->value, false, $character->getUnlockCost(), $lockedUser->getScrap());
        });
    }
}
