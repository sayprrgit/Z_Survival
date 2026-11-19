<?php

declare(strict_types=1);

namespace App\Game\Character;

use App\Enum\CharacterCode;
use App\Enum\WeaponCode;

final class Boris extends PlayableCharacter
{
    public function __construct()
    {
        parent::__construct(
            CharacterCode::Boris,
            'Boris',
            'Mécanicien',
            'Un colosse lent mais extrêmement résistant, conçu pour le combat rapproché.',
            '-20 % de vitesse et -20 % de dégâts subis.',
            new StatBlock(maxHealth: 180, movementSpeedBonus: -20, damageReduction: 20),
            WeaponCode::Mace,
        );
    }
}
