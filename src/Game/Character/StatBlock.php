<?php

declare(strict_types=1);

namespace App\Game\Character;

final readonly class StatBlock
{
    public function __construct(
        public int $maxHealth,
        public float $damageBonus = 0,
        public float $attackSpeedBonus = 0,
        public float $movementSpeedBonus = 0,
        public float $criticalChanceBonus = 0,
        public float $damageReduction = 0,
        public float $healthRegeneration = 0,
        public float $healingEfficiencyBonus = 0,
    ) {
    }
}
