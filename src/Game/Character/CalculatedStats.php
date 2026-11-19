<?php

declare(strict_types=1);

namespace App\Game\Character;

final readonly class CalculatedStats
{
    public function __construct(
        public int $maxHealth,
        public float $damageBonus,
        public float $attackSpeedBonus,
        public float $movementSpeedBonus,
        public float $criticalChanceBonus,
        public float $damageReduction,
        public float $healthRegeneration,
        public float $healingBonus,
        public float $pickupRangeBonus,
        public float $experienceBonus,
        public float $lootChanceBonus,
        public float $scrapBonus,
    ) {
    }
}
