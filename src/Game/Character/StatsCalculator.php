<?php

declare(strict_types=1);

namespace App\Game\Character;

/** Combines character passives with the permanent effects a user has purchased. */
final class StatsCalculator
{
    /** @param array<string, float> $talentEffects */
    public function calculate(PlayableCharacter $character, array $talentEffects): CalculatedStats
    {
        $base = $character->stats;
        $effect = static fn (string $type): float => $talentEffects[$type] ?? 0.0;

        return new CalculatedStats(
            maxHealth: max(1, $base->maxHealth + (int) $effect('max_health')),
            damageBonus: $base->damageBonus / 100 + $character->damageBonus + $effect('damage_bonus'),
            attackSpeedBonus: $base->attackSpeedBonus / 100 + $character->attackSpeedBonus + $effect('attack_speed_bonus'),
            movementSpeedBonus: $base->movementSpeedBonus / 100 + $character->movementSpeedBonus + $effect('movement_speed_bonus'),
            criticalChanceBonus: $base->criticalChanceBonus / 100 + $character->criticalChanceBonus + $effect('critical_chance_bonus'),
            damageReduction: min(0.8, $base->damageReduction / 100 + $character->damageReduction + $effect('damage_reduction')),
            healthRegeneration: max(0, $base->healthRegeneration + $character->healthRegeneration + $effect('health_regeneration')),
            healingBonus: $base->healingEfficiencyBonus / 100 + $effect('healing_effectiveness_bonus'),
            pickupRangeBonus: $effect('pickup_range_bonus'),
            experienceBonus: $effect('experience_bonus'),
            lootChanceBonus: $effect('loot_chance_bonus'),
            scrapBonus: $effect('scrap_bonus'),
        );
    }
}
