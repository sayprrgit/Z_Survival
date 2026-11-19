<?php

declare(strict_types=1);

namespace App\Game\Run;

use App\DTO\GameConfiguration;
use App\Entity\Character;
use App\Repository\PowerUpRepository;
use App\Repository\WeaponRepository;

final readonly class GameConfigurationBuilder
{
    public function __construct(private WeaponRepository $weapons, private PowerUpRepository $powerUps)
    {
    }

    public function build(Character $character): GameConfiguration
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
            'name' => $character->getName(), 'weapon' => $character->getStartingWeapon()->getCode()->value,
            'maxHealth' => $character->getBaseHealth(), 'damageBonus' => $character->getDamageBonus(),
            'attackSpeedBonus' => $character->getAttackSpeedBonus(), 'movementSpeedBonus' => $character->getMovementSpeedBonus(),
            'criticalChanceBonus' => $character->getCriticalChanceBonus(), 'damageReduction' => $character->getDamageReduction(),
            'healthRegeneration' => $character->getHealthRegeneration(), 'healingBonus' => $character->getHealingEffectivenessBonus(),
        ], $weapons, $powerUps);
    }
}
