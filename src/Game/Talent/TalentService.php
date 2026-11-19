<?php

declare(strict_types=1);

namespace App\Game\Talent;

use App\DTO\TalentPurchaseReceipt;
use App\Entity\User;
use App\Entity\UserTalent;
use App\Repository\TalentRepository;
use App\Repository\UserTalentRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TalentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TalentRepository $talents,
        private UserTalentRepository $userTalents,
    ) {
    }

    public function purchase(User $user, string $talentCode): TalentPurchaseReceipt
    {
        if ($user->getId() === null) {
            throw new \DomainException('Un compte enregistré est nécessaire pour acheter un talent.');
        }

        return $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($user, $talentCode): TalentPurchaseReceipt {
            $lockMode = $entityManager->getConnection()->getDatabasePlatform() instanceof SQLitePlatform
                ? LockMode::NONE
                : LockMode::PESSIMISTIC_WRITE;
            $lockedUser = $entityManager->find(User::class, $user->getId(), $lockMode);
            if (!$lockedUser instanceof User) {
                throw new \DomainException('Compte introuvable.');
            }

            $talent = $this->talents->findByCode($talentCode);
            if ($talent === null) {
                throw new \DomainException('Talent introuvable.');
            }

            if ($this->userTalents->owns($lockedUser, $talent)) {
                return new TalentPurchaseReceipt($talentCode, true, 0, $lockedUser->getScrap());
            }

            $parent = $talent->getParent();
            if ($parent !== null && !$this->userTalents->owns($lockedUser, $parent)) {
                throw new \DomainException('Achète d’abord le talent précédent de cette branche.');
            }

            $lockedUser->spendScrap($talent->getCost());
            $userTalent = new UserTalent($lockedUser, $talent);
            $entityManager->persist($userTalent);
            $entityManager->flush();

            return new TalentPurchaseReceipt($talentCode, false, $talent->getCost(), $lockedUser->getScrap());
        });
    }
}
