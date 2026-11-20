<?php

declare(strict_types=1);

namespace App\Game\Character;

use App\Enum\CharacterCode;
use App\Enum\WeaponCode;

final class Mason extends PlayableCharacter
{
    public function __construct()
    {
        parent::__construct(
            CharacterCode::Mason,
            'Mason',
            'Soldat',
            'Un ancien soldat polyvalent, idéal pour tenir la ligne face aux hordes.',
            '+15 % de dégâts et +10 % de cadence de tir.',
            new StatBlock(maxHealth: 120, damageBonus: 15, attackSpeedBonus: 10),
            WeaponCode::AssaultRifle,
        );
    }
}
