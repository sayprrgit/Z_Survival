import test from 'node:test';
import assert from 'node:assert/strict';
import { GameState } from '../assets/game/core/GameState.js';
import { CombatSystem } from '../assets/game/systems/CombatSystem.js';
import { Enemy } from '../assets/game/entities/Enemy.js';
import { DifficultySystem } from '../assets/game/systems/DifficultySystem.js';
import { SpawnSystem } from '../assets/game/systems/SpawnSystem.js';
import { CollisionSystem } from '../assets/game/systems/CollisionSystem.js';
import { EnemyAiSystem } from '../assets/game/systems/EnemyAiSystem.js';
import { LootSystem } from '../assets/game/systems/LootSystem.js';
import { MovementSystem } from '../assets/game/systems/MovementSystem.js';
import { Projectile } from '../assets/game/entities/Projectile.js';
import { Camera } from '../assets/game/rendering/Camera.js';
import { Renderer } from '../assets/game/rendering/Renderer.js';
import { InputManager } from '../assets/game/core/InputManager.js';

const weaponCodes = ['pistol', 'assault_rifle', 'shotgun', 'mace', 'knife', 'molotov'];
const powerUps = [
    ['reinforced_ammo', 'damage_bonus', 0.1], ['reflexes', 'attack_speed_bonus', 0.08], ['reinforced_vest', 'max_health', 15],
    ['adrenaline', 'movement_speed_bonus', 0.05], ['precision', 'critical_chance_bonus', 0.05], ['regeneration', 'health_regeneration', 0.3],
    ['magnet', 'pickup_range_bonus', 0.15], ['survival_instinct', 'experience_bonus', 0.1],
];

function configuration() {
    return {
        player: { code: 'mason', name: 'Test', weapon: 'pistol', maxHealth: 100, damageBonus: 0.15, attackSpeedBonus: 0.1, movementSpeedBonus: 0, criticalChanceBonus: 0, damageReduction: 0, healthRegeneration: 0, healingBonus: 0 },
        weapons: weaponCodes.map((code) => ({ code, name: code, description: '', attackType: 'targeted', damage: 10, interval: 1, range: 300, projectileCount: 1, pierce: 0, area: 0, maxLevel: 5 })),
        powerUps: powerUps.map(([code, effect, bonus]) => ({ code, name: code, description: '', effect, bonus, maxLevel: 5 })),
        enemies: [
            { code: 'walker', name: 'Walker', boss: false, health: 25, damage: 8, speed: 32, range: 24, interval: 1.4, weight: 10, availableAt: 0, experience: 5 },
            { code: 'runner', name: 'Runner', boss: false, health: 15, damage: 7, speed: 68, range: 22, interval: 1, weight: 5, availableAt: 120, experience: 7 },
            { code: 'colossus', name: 'Le Colosse', boss: true, health: 1000, damage: 35, speed: 38, range: 42, interval: 1.6, weight: 0, availableAt: 300, experience: 100 },
            { code: 'patient_zero', name: 'Patient Zero', boss: true, health: 4000, damage: 40, speed: 30, range: 220, interval: 1.8, weight: 0, availableAt: 900, experience: 250 },
        ],
        difficulties: [{ code: 'normal', name: 'Normal', multiplier: 1 }, { code: 'hard', name: 'Difficile', multiplier: 1.3 }],
        difficulty: 'normal',
    };
}

test('weapon shortcuts use physical number keys on AZERTY and numpad keyboards', () => {
    const listeners = new Map();
    const target = { addEventListener: (type, callback) => listeners.set(type, callback), removeEventListener: () => {} };
    const input = new InputManager(target);
    let prevented = false;
    listeners.get('keydown')({ key: '&', code: 'Digit1', preventDefault: () => { prevented = true; } });
    assert.equal(input.consumeWeaponSlot(), 0);
    assert.equal(prevented, true);
    listeners.get('keydown')({ key: '4', code: 'Numpad4', preventDefault: () => {} });
    assert.equal(input.consumeWeaponSlot(), 3);
    listeners.get('wheel')({ deltaY: -12, preventDefault: () => { prevented = true; } });
    assert.equal(input.consumeWeaponSwitchDirection(), -1);
    listeners.get('wheel')({ deltaY: 12, preventDefault: () => {} });
    assert.equal(input.consumeWeaponSwitchDirection(), 1);
    input.destroy();
});

test('configuration exposes every catalog weapon and power-up', () => {
    const state = new GameState(configuration());
    assert.deepEqual([...state.definitions.weaponsByCode.keys()].sort(), [...weaponCodes].sort());
    assert.equal(state.definitions.powerUpsByCode.size, 8);
    assert.equal(state.player.weapons.has('pistol'), true);
    assert.equal(state.player.weapons.has('knife'), true);
    state.player.selectWeapon('knife');
    assert.equal(state.player.activeWeaponCode, 'knife');
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
    assert.equal('pickupRadius' in state.player, false);
    assert.equal('xp' in state.player, false);
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
        state.player.weapons.set(code, { level: 1, cooldown: 0, ammo: 30, reserve: 60, magazine: 30, reload: 0 });
        state.player.activeWeaponCode = code;
        const target = new Enemy(25, 0, config.enemies[0]);
        state.enemies.push(target);
        new CombatSystem().update(state, 0.01, true);
        if (['targeted', 'rapid', 'spread'].includes(attackType)) assert.ok(state.projectiles.length > 0, `${code} should launch projectiles`);
        if (attackType === 'melee_area' || attackType === 'melee') assert.ok(target.health < target.maxHealth, `${code} should hit immediately`);
        if (attackType === 'area_damage') assert.equal(state.projectiles.length, 1, `${code} should launch its delayed explosive`);
    }
});

test('spawn system uses catalog availability, weight and boss timing', () => {
    const state = new GameState(configuration());
    const difficulty = new DifficultySystem(state.configuration.difficulties, 'hard');
    const spawner = new SpawnSystem(state, difficulty);
    spawner.update(0.1, 900, 600);
    assert.equal(state.enemies[0].code, 'walker');
    assert.equal(state.enemies[0].maxHealth, Math.ceil(25 * 1.3));

    state.elapsed = 300;
    state.spawnTimer = 1;
    spawner.update(0.1, 900, 600);
    assert.equal(state.bossesSpawned.has('colossus'), true);
    assert.equal(state.enemies.some((enemy) => enemy.code === 'colossus' && enemy.isBoss), true);
    assert.equal(state.finalBossCode, 'patient_zero');

    state.enemies = Array.from({ length: 80 }, () => new Enemy(0, 0, state.definitions.enemiesByCode.get('walker')));
    state.wave = 1;
    state.spawnTimer = 0;
    spawner.update(0.1, 900, 600);
    assert.equal(state.enemies.length, 80);
});

test('difficulty drives waves and increases threat over time', () => {
    const difficulty = new DifficultySystem(configuration().difficulties, 'hard');
    assert.equal(difficulty.waveAt(0), 1);
    assert.equal(difficulty.waveAt(90), 3);
    assert.ok(difficulty.enemyScaleAt(600) > difficulty.enemyScaleAt(0));
    assert.ok(difficulty.spawnIntervalAt(600) < difficulty.spawnIntervalAt(0));
});

test('core loop systems move, attack, collide and reward loot without experience orbs', () => {
    const state = new GameState(configuration());
    const enemy = new Enemy(90, 0, state.definitions.enemiesByCode.get('walker'));
    state.enemies.push(enemy);

    new MovementSystem().update(state.player, { x: 1, y: 0 }, 1);
    assert.equal(state.player.x, state.player.moveSpeed);

    state.player.x = 0;
    new EnemyAiSystem().update(state, 0.1);
    assert.ok(enemy.x < 90, 'enemy should approach the survivor');

    const healthBefore = enemy.health;
    state.projectiles.push(new Projectile(enemy.x, enemy.y, 0, 0, 10));
    new CollisionSystem().update(state, 0.01);
    assert.equal(enemy.health, healthBefore - 10);

    enemy.takeDamage(1000);
    const defeated = new LootSystem().rewardDefeatedEnemies(state);
    assert.equal(defeated.length, 1);
    assert.equal(state.kills, 1);
    assert.equal(state.orbs.length, 0);
});

test('pause reasons do not accidentally resume an upgrade or end screen', () => {
    const state = new GameState(configuration());
    state.pause('manual');
    state.pause('upgrade');
    state.resume('manual');
    assert.equal(state.paused, true);
    state.resume('upgrade');
    assert.equal(state.paused, false);
});

test('renderer paints a playable scene even while image assets are loading', () => {
    const originalImage = globalThis.Image;
    const originalWindow = globalThis.window;
    const calls = [];
    globalThis.Image = class { constructor() { this.complete = false; this.naturalWidth = 0; } set src(value) { this.source = value; } };
    globalThis.window = { devicePixelRatio: 1 };
    const context = {
        clearRect: () => calls.push('clear'), fillRect: () => calls.push('fill'), beginPath: () => {}, closePath: () => {}, moveTo: () => {}, lineTo: () => {}, stroke: () => {}, arc: () => {}, ellipse: () => {}, fill: () => {}, save: () => {}, restore: () => {}, translate: () => {}, clip: () => {}, drawImage: () => {}, fillText: () => {}, setTransform: () => {},
        createRadialGradient: () => ({ addColorStop: () => {} }),
    };
    const canvas = { width: 0, height: 0, getContext: () => context, getBoundingClientRect: () => ({ width: 900, height: 500 }) };
    try {
        const renderer = new Renderer(canvas, new Camera());
        renderer.resize();
        const state = new GameState(configuration());
        state.enemies.push(new Enemy(80, 0, state.definitions.enemiesByCode.get('walker')));
        renderer.render(state);
        assert.ok(calls.includes('clear'));
        assert.ok(calls.includes('fill'));
    } finally {
        globalThis.Image = originalImage;
        globalThis.window = originalWindow;
    }
});
