import test from 'node:test';
import assert from 'node:assert/strict';
import { GameState } from '../assets/game/core/GameState.js';
import { CombatSystem } from '../assets/game/systems/CombatSystem.js';
import { Enemy } from '../assets/game/entities/Enemy.js';

const weaponCodes = ['pistol', 'assault_rifle', 'shotgun', 'mace', 'knife', 'molotov'];
const powerUps = [
    ['reinforced_ammo', 'damage_bonus', 0.1], ['reflexes', 'attack_speed_bonus', 0.08], ['reinforced_vest', 'max_health', 15],
    ['adrenaline', 'movement_speed_bonus', 0.05], ['precision', 'critical_chance_bonus', 0.05], ['regeneration', 'health_regeneration', 0.3],
    ['magnet', 'pickup_range_bonus', 0.15], ['survival_instinct', 'experience_bonus', 0.1],
];

function configuration() {
    return {
        player: { name: 'Test', weapon: 'pistol', maxHealth: 100, damageBonus: 0.15, attackSpeedBonus: 0.1, movementSpeedBonus: 0, criticalChanceBonus: 0, damageReduction: 0, healthRegeneration: 0, healingBonus: 0 },
        weapons: weaponCodes.map((code) => ({ code, name: code, description: '', attackType: 'targeted', damage: 10, interval: 1, range: 300, projectileCount: 1, pierce: 0, area: 0, maxLevel: 5 })),
        powerUps: powerUps.map(([code, effect, bonus]) => ({ code, name: code, description: '', effect, bonus, maxLevel: 5 })),
    };
}

test('configuration exposes every catalog weapon and power-up', () => {
    const state = new GameState(configuration());
    assert.deepEqual([...state.definitions.weaponsByCode.keys()].sort(), [...weaponCodes].sort());
    assert.equal(state.definitions.powerUpsByCode.size, 8);
    assert.equal(state.player.weapons.has('pistol'), true);
});

test('power-up levels recalculate stats without compounding previous levels', () => {
    const state = new GameState(configuration());
    const ammo = state.definitions.powerUpsByCode.get('reinforced_ammo');
    state.player.addPowerUp(ammo);
    state.player.addPowerUp(ammo);
    assert.ok(Math.abs(state.player.damageMultiplier - 1.35) < 1e-9);
    assert.equal(state.player.powerUps.get('reinforced_ammo').level, 2);
});

test('all catalog power-up effect types are handled by the runtime', () => {
    const state = new GameState(configuration());
    for (const powerUp of state.definitions.powerUpsByCode.values()) state.player.addPowerUp(powerUp);
    assert.equal(state.player.maxHealth, 115);
    assert.ok(state.player.moveSpeed > 175);
    assert.equal(state.player.criticalChance, 0.05);
    assert.ok(state.player.regeneration > 0);
    assert.ok(state.player.pickupRadius > 90);
    assert.ok(state.player.bonuses.xp > 0);
});

test('each of the six weapons executes its configured attack style', () => {
    const styles = new Map([
        ['pistol', 'targeted'], ['assault_rifle', 'rapid'], ['shotgun', 'spread'],
        ['mace', 'melee_area'], ['knife', 'melee'], ['molotov', 'area_damage'],
    ]);
    for (const [code, attackType] of styles) {
        const config = configuration();
        config.weapons = config.weapons.map((weapon) => weapon.code === code ? { ...weapon, attackType, area: 120, range: 380 } : weapon);
        const state = new GameState(config);
        state.player.weapons.clear();
        state.player.weapons.set(code, { level: 1, cooldown: 0 });
        const target = new Enemy(25, 0);
        state.enemies.push(target);
        new CombatSystem().update(state, 0.01);
        if (['targeted', 'rapid', 'spread'].includes(attackType)) assert.ok(state.projectiles.length > 0, `${code} should launch projectiles`);
        if (attackType === 'melee_area' || attackType === 'melee') assert.ok(target.health < target.maxHealth, `${code} should hit immediately`);
        if (attackType === 'area_damage') assert.equal(state.fireZones.length, 1, `${code} should create a damage zone`);
    }
});
