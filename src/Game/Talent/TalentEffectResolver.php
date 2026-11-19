<?php

declare(strict_types=1);

namespace App\Game\Talent;

use App\Entity\User;
use App\Repository\UserTalentRepository;

final class TalentEffectResolver
{
    public function __construct(private readonly UserTalentRepository $userTalents)
    {
    }

    /** @return array<string, float> */
    public function effectsFor(User $user): array
    {
        $effects = [];
        foreach ($this->userTalents->findAllForUser($user) as $userTalent) {
            $talent = $userTalent->getTalent();
            $effects[$talent->getEffectType()] = ($effects[$talent->getEffectType()] ?? 0.0) + $talent->getEffectValue();
        }

        return $effects;
    }
}
