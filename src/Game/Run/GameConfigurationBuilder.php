<?php

declare(strict_types=1);

namespace App\Game\Run;

use App\DTO\GameConfiguration;
use App\Game\Character\PlayableCharacter;
use App\Repository\PowerUpRepository;
use App\Repository\WeaponRepository;

final readonly class GameConfigurationBuilder
{
    public function __construct(private WeaponRepository $weapons, private PowerUpRepository $powerUps)
    {
    }

    public function build(PlayableCharacter $character): GameConfiguration
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

        return new GameConfiguration([
            'code' => $character->code->value, 'name' => $character->name, 'weapon' => $character->startingWeapon->value,
            'maxHealth' => $character->stats->maxHealth, 'damageBonus' => $character->stats->damageBonus / 100,
            'attackSpeedBonus' => $character->stats->attackSpeedBonus / 100, 'movementSpeedBonus' => $character->stats->movementSpeedBonus / 100,
            'criticalChanceBonus' => $character->stats->criticalChanceBonus / 100, 'damageReduction' => $character->stats->damageReduction / 100,
            'healthRegeneration' => $character->stats->healthRegeneration, 'healingBonus' => $character->stats->healingEfficiencyBonus / 100,
        ], $weapons, $powerUps);
    }
}
