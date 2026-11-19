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

    public function build(PlayableCharacter $character, User $user): GameConfiguration
    {
        $weapons = array_map(static fn ($weapon): array => [
            'code' => $weapon->getCode()->value, 'name' => $weapon->getName(), 'description' => $weapon->getDescription(),
            'attackType' => $weapon->getAttackType(), 'damage' => $weapon->getBaseDamage(), 'interval' => $weapon->getAttackInterval(),
            'range' => $weapon->getRange(), 'projectileCount' => $weapon->getProjectileCount(), 'pierce' => $weapon->getPierceCount(),
            'area' => $weapon->getAreaOfEffect(), 'maxLevel' => $weapon->getMaxLevel(),
        ], $this->weapons->findBy([], ['code' => 'ASC']));
        $powerUps = array_map(static fn ($powerUp): array => [
            'code' => $powerUp->getCode()->value, 'name' => $powerUp->getName(), 'description' => $powerUp->getDescription(),
            'effect' => $powerUp->getEffectType(), 'bonus' => $powerUp->getBonusPerLevel(), 'maxLevel' => $powerUp->getMaxLevel(),
        ], $this->powerUps->findBy([], ['code' => 'ASC']));
        $enemies = array_map(static fn ($enemy): array => [
            'code' => $enemy->getCode()->value,
            'name' => $enemy->getName(),
            'description' => $enemy->getDescription(),
            'maxHealth' => $enemy->getBaseHealth(),
            'damage' => $enemy->getBaseDamage(),
            'speed' => $enemy->getMovementSpeed(),
            'range' => $enemy->getAttackRange(),
            'interval' => $enemy->getAttackInterval(),
            'isBoss' => $enemy->isBoss(),
            'spawnWeight' => $enemy->getSpawnWeight(),
            'availableFromSeconds' => $enemy->getAvailableFromSeconds(),
            'experienceReward' => $enemy->getExperienceReward(),
            'scrapDropChance' => $enemy->getScrapDropChance(),
            'minimumScrapDrop' => $enemy->getMinimumScrapDrop(),
            'maximumScrapDrop' => $enemy->getMaximumScrapDrop(),
            'spritePath' => $enemy->getSpritePath(),
        ], $this->enemies->findBy([], ['code' => 'ASC']));
        $difficulties = array_map(static fn ($difficulty): array => [
            'code' => $difficulty->getCode()->value,
            'name' => $difficulty->getName(),
            'description' => $difficulty->getDescription(),
            'multiplier' => $difficulty->getMultiplier(),
            'sortOrder' => $difficulty->getSortOrder(),
        ], $this->difficulties->findBy([], ['sortOrder' => 'ASC']));
        $stats = $this->statsCalculator->calculate($character, $this->talentEffects->effectsFor($user));

        return new GameConfiguration([
            'code' => $character->code->value, 'name' => $character->name, 'weapon' => $character->startingWeapon->value,
            'maxHealth' => $stats->maxHealth, 'damageBonus' => $stats->damageBonus,
            'attackSpeedBonus' => $stats->attackSpeedBonus, 'movementSpeedBonus' => $stats->movementSpeedBonus,
            'criticalChanceBonus' => $stats->criticalChanceBonus, 'damageReduction' => $stats->damageReduction,
            'healthRegeneration' => $stats->healthRegeneration, 'healingBonus' => $stats->healingBonus,
            'pickupRangeBonus' => $stats->pickupRangeBonus, 'experienceBonus' => $stats->experienceBonus,
            'lootChanceBonus' => $stats->lootChanceBonus, 'scrapBonus' => $stats->scrapBonus,
        ], $weapons, $powerUps, $enemies, $difficulties);
    }
}
