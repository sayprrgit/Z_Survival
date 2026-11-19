<?php

declare(strict_types=1);

namespace App\Game\Talent;

use App\DTO\TalentCatalogItem;
use App\Entity\User;
use App\Repository\TalentRepository;
use App\Repository\UserTalentRepository;

final readonly class TalentTree
{
    public function __construct(private TalentRepository $talents, private UserTalentRepository $userTalents)
    {
    }

    /** @return list<TalentCatalogItem> */
    public function catalogFor(User $user): array
    {
        $ownedCodes = $this->userTalents->findCodesForUser($user);
        $owned = array_fill_keys($ownedCodes, true);
        $items = [];

        foreach ($this->talents->findBy([], ['branch' => 'ASC', 'tier' => 'ASC']) as $talent) {
            $parentCode = $talent->getParent()?->getCode();
            $isOwned = isset($owned[$talent->getCode()]);
            $prerequisiteMet = $parentCode === null || isset($owned[$parentCode]);
            $items[] = new TalentCatalogItem(
                $talent->getCode(),
                $talent->getName(),
                $talent->getDescription(),
                $talent->getBranch()->value,
                $talent->getTier(),
                $talent->getCost(),
                $talent->getEffectType(),
                $talent->getEffectValue(),
                $parentCode,
                $isOwned,
                !$isOwned && $prerequisiteMet && $user->getScrap() >= $talent->getCost(),
            );
        }

        return $items;
    }
}
