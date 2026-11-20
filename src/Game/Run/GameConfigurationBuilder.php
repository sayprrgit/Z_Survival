<?php

declare(strict_types=1);

namespace App\Game\Run;

use App\DTO\GameConfiguration;
use App\Entity\User;
use App\Game\Character\PlayableCharacter;
use App\Game\Character\StatsCalculator;
use App\Game\Talent\TalentEffectResolver;
use App\Repository\DifficultyRepository;
use App\Repository\EnemyTypeRepository;
use App\Repository\PowerUpRepository;
use App\Repository\WeaponRepository;

final readonly class GameConfigurationBuilder
{
    public function __construct(
        private WeaponRepository $weapons,
        private PowerUpRepository $powerUps,
        private EnemyTypeRepository $enemies,
        private DifficultyRepository $difficulties,
        private TalentEffectResolver $talentEffects,
        private StatsCalculator $statsCalculator,
    ) {
    }

    public function build(PlayableCharacter $character, User $user, string $difficulty = 'normal'): GameConfiguration
    {
        $weapons = array_map(static fn ($weapon): array => [
            'code' => $weapon->getCode()->value,
            'name' => $weapon->getName(),
            'description' => $weapon->getDescription(),
            'attackType' => $weapon->getAttackType(),
            'damage' => $weapon->getBaseDamage(),
            'interval' => $weapon->getAttackInterval(),
            'range' => $weapon->getRange(),
            'projectileCount' => $weapon->getProjectileCount(),
            'pierce' => $weapon->getPierceCount(),
            'area' => $weapon->getAreaOfEffect(),
            'maxLevel' => $weapon->getMaxLevel(),
        ], $this->weapons->findBy([], ['code' => 'ASC']));
        $powerUps = array_map(static fn ($powerUp): array => [
            'code' => $powerUp->getCode()->value,
            'name' => $powerUp->getName(),
            'description' => $powerUp->getDescription(),
            'effect' => $powerUp->getEffectType(),
            'bonus' => $powerUp->getBonusPerLevel(),
            'maxLevel' => $powerUp->getMaxLevel(),
        ], $this->powerUps->findBy([], ['code' => 'ASC']));
        $enemies = array_map(static fn ($enemy): array => [
            'code' => $enemy->getCode()->value,
            'name' => $enemy->getName(),
            'description' => $enemy->getDescription(),
            'boss' => $enemy->isBoss(),
            'isBoss' => $enemy->isBoss(),
            'health' => $enemy->getBaseHealth(),
            'maxHealth' => $enemy->getBaseHealth(),
            'damage' => $enemy->getBaseDamage(),
            'speed' => $enemy->getMovementSpeed(),
            'range' => $enemy->getAttackRange(),
            'interval' => $enemy->getAttackInterval(),
            'weight' => $enemy->getSpawnWeight(),
            'spawnWeight' => $enemy->getSpawnWeight(),
            'availableAt' => $enemy->getAvailableFromSeconds(),
            'availableFromSeconds' => $enemy->getAvailableFromSeconds(),
            'experience' => $enemy->getExperienceReward(),
            'experienceReward' => $enemy->getExperienceReward(),
            'scrapChance' => $enemy->getScrapDropChance(),
            'minimumScrap' => $enemy->getMinimumScrapDrop(),
            'maximumScrap' => $enemy->getMaximumScrapDrop(),
            'spritePath' => $enemy->getSpritePath(),
        ], $this->enemies->findAllForGame());
        $difficulties = array_map(static fn ($item): array => [
            'code' => $item->getCode()->value,
            'name' => $item->getName(),
            'description' => $item->getDescription(),
            'multiplier' => $item->getMultiplier(),
            'sortOrder' => $item->getSortOrder(),
        ], $this->difficulties->findAllForGame());
        $stats = $this->statsCalculator->calculate($character, $this->talentEffects->effectsFor($user));

        return new GameConfiguration([
            'code' => $character->code->value,
            'name' => $character->name,
            'weapon' => $character->startingWeapon->value,
            'maxHealth' => $stats->maxHealth,
            'damageBonus' => $stats->damageBonus,
            'attackSpeedBonus' => $stats->attackSpeedBonus,
            'movementSpeedBonus' => $stats->movementSpeedBonus,
            'criticalChanceBonus' => $stats->criticalChanceBonus,
            'damageReduction' => $stats->damageReduction,
            'healthRegeneration' => $stats->healthRegeneration,
            'healingBonus' => $stats->healingBonus,
            'pickupRangeBonus' => $stats->pickupRangeBonus,
            'experienceBonus' => $stats->experienceBonus,
            'lootChanceBonus' => $stats->lootChanceBonus,
            'scrapBonus' => $stats->scrapBonus,
        ], $weapons, $powerUps, $enemies, $difficulties, $difficulty);
    }
}
