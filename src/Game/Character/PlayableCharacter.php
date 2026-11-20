<?php

declare(strict_types=1);

namespace App\Game\Character;

use App\Enum\CharacterCode;
use App\Enum\WeaponCode;

abstract class PlayableCharacter
{
    public function __construct(
        public readonly CharacterCode $code,
        public readonly string $name,
        public readonly string $role,
        public readonly string $description,
        public readonly string $passiveDescription,
        public readonly StatBlock $stats,
        public readonly WeaponCode $startingWeapon,
        public readonly float $damageBonus = 0,
        public readonly float $attackSpeedBonus = 0,
        public readonly float $movementSpeedBonus = 0,
        public readonly float $criticalChanceBonus = 0,
        public readonly float $damageReduction = 0,
        public readonly float $healthRegeneration = 0,
    ) {
    }

    public function getStartingWeaponLabel(): string
    {
        return match ($this->startingWeapon) {
            WeaponCode::Pistol => 'Pistolet',
            WeaponCode::AssaultRifle => 'Fusil d’assaut',
            WeaponCode::Shotgun => 'Fusil à pompe',
            WeaponCode::Mace => 'Masse',
            WeaponCode::Knife => 'Couteau',
            WeaponCode::Molotov => 'Molotov',
        };
    }
}
