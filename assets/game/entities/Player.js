import { Actor } from './Actor.js';

export class Player extends Actor {
    constructor(configuration) {
        super(0, 0, 15, configuration.player.maxHealth);
        this.base = configuration.player;
        this.level = 1;
        this.weapons = new Map();
        this.powerUps = new Map();
        this.facing = { x: 1, y: 0 };
        this.isMoving = false;
        this.activeWeaponCode = null;
        this.addWeapon(this.base.weapon);
        this.addWeapon(this.base.weapon === 'pistol' ? 'knife' : 'pistol');
        this.recalculate();
    }

    addWeapon(code) {
        if (this.weapons.has(code)) this.weapons.get(code).level++;
        else {
            const melee = code === 'mace' || code === 'knife';
            const magazine = melee ? Infinity : ({ pistol: 12, assault_rifle: 30, shotgun: 8, molotov: 3 }[code] ?? 12);
            this.weapons.set(code, { level: 1, cooldown: 0, ammo: magazine, reserve: melee ? Infinity : magazine * 3, magazine, reload: 0 });
            this.activeWeaponCode ??= code;
        }
        this.recalculate();
    }

    get activeWeapon() { return this.weapons.get(this.activeWeaponCode); }

    switchWeapon(direction = 1) {
        const codes = [...this.weapons.keys()];
        if (codes.length < 2) return;
        const current = Math.max(0, codes.indexOf(this.activeWeaponCode));
        this.activeWeaponCode = codes[(current + direction + codes.length) % codes.length];
    }

    selectWeapon(code) {
        if (this.weapons.has(code)) this.activeWeaponCode = code;
    }

    reloadWeapon() {
        const weapon = this.activeWeapon;
        if (!weapon || weapon.reload > 0 || weapon.ammo >= weapon.magazine || weapon.reserve <= 0 || !Number.isFinite(weapon.magazine)) return;
        weapon.reload = weapon.magazine <= 8 ? 1.35 : 1.8;
    }

    updateWeapons(dt) {
        for (const weapon of this.weapons.values()) {
            weapon.cooldown = Math.max(0, weapon.cooldown - dt);
            if (weapon.reload <= 0) continue;
            weapon.reload = Math.max(0, weapon.reload - dt);
            if (weapon.reload === 0) {
                const loaded = Math.min(weapon.magazine - weapon.ammo, weapon.reserve);
                weapon.ammo += loaded;
                weapon.reserve -= loaded;
            }
        }
    }

    addAmmo(amount) {
        for (const weapon of this.weapons.values()) if (Number.isFinite(weapon.reserve)) weapon.reserve = Math.min(weapon.reserve + amount, weapon.magazine * 8);
    }

    addPowerUp(definition) {
        const item = this.powerUps.get(definition.code) ?? { level: 0 };
        item.level++;
        this.powerUps.set(definition.code, item);
        if (definition.effect === 'max_health') {
            const increase = definition.bonus;
            this.maxHealth += increase;
            this.health += increase;
        }
        this.recalculate();
    }

    recalculate() {
        const totals = { damage: 0, speed: 0, move: 0, crit: 0, regen: 0 };
        for (const [code, item] of this.powerUps) {
            const definition = this.definitions?.powerUpsByCode.get(code);
            if (!definition) continue;
            const value = definition.bonus * item.level;
            if (definition.effect === 'damage_bonus') totals.damage += value;
            if (definition.effect === 'attack_speed_bonus') totals.speed += value;
            if (definition.effect === 'movement_speed_bonus') totals.move += value;
            if (definition.effect === 'critical_chance_bonus') totals.crit += value;
            if (definition.effect === 'health_regeneration') totals.regen += value;
        }
        this.bonuses = totals;
        this.damageMultiplier = 1 + this.base.damageBonus + totals.damage;
        this.attackSpeedMultiplier = 1 + this.base.attackSpeedBonus + totals.speed;
        this.moveSpeed = 175 * (1 + this.base.movementSpeedBonus + totals.move);
        this.criticalChance = Math.min(0.75, this.base.criticalChanceBonus + totals.crit);
        this.regeneration = this.base.healthRegeneration + totals.regen;
    }

    attachDefinitions(definitions) {
        this.definitions = definitions;
        this.recalculate();
    }
}
