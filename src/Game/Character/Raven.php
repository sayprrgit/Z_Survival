<?php

declare(strict_types=1);

namespace App\Game\Character;

use App\Enum\CharacterCode;
use App\Enum\WeaponCode;

final class Raven extends PlayableCharacter
{
    public function __construct()
    {
        parent::__construct(
            CharacterCode::Raven,
            'Raven',
            'Éclaireuse',
            'Une survivante agile qui élimine les cibles prioritaires avec précision.',
            '+25 % de vitesse et +15 points de chance critique.',
            new StatBlock(maxHealth: 80, movementSpeedBonus: 25, criticalChanceBonus: 15),
            WeaponCode::Pistol,
        );
    }
}
