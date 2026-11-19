<?php

declare(strict_types=1);

namespace App\Game\Character;

use App\Enum\CharacterCode;
use App\Enum\WeaponCode;

final class Eva extends PlayableCharacter
{
    public function __construct()
    {
        parent::__construct(
            CharacterCode::Eva,
            'Eva',
            'Médecin',
            'Une soignante qui compense sa faible portée par une excellente survie.',
            'Régénération naturelle et +25 % d’efficacité des soins.',
            new StatBlock(maxHealth: 100, healthRegeneration: 0.3, healingEfficiencyBonus: 25),
            WeaponCode::Knife,
        );
    }
}
