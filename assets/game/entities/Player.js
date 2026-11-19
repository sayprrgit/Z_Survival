import { Actor } from './Actor.js';

export class Player extends Actor {
    constructor(configuration) {
        super(0, 0, 15, configuration.player.maxHealth);
        this.base = configuration.player;
        this.weapons = new Map();
        this.powerUps = new Map();
        this.xp = 0;
        this.level = 1;
        this.xpToNext = 5;
        this.pickupRadius = 90;
        this.addWeapon(this.base.weapon);
        this.recalculate();
    }

    addWeapon(code) {
        if (this.weapons.has(code)) this.weapons.get(code).level++;
        else this.weapons.set(code, { level: 1, cooldown: 0 });
        this.recalculate();
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
        const totals = { damage: 0, speed: 0, move: 0, crit: 0, regen: 0, pickup: 0, xp: 0 };
        for (const [code, item] of this.powerUps) {
            const definition = this.definitions?.powerUpsByCode.get(code);
            if (!definition) continue;
            const value = definition.bonus * item.level;
            if (definition.effect === 'damage_bonus') totals.damage += value;
            if (definition.effect === 'attack_speed_bonus') totals.speed += value;
            if (definition.effect === 'movement_speed_bonus') totals.move += value;
            if (definition.effect === 'critical_chance_bonus') totals.crit += value;
            if (definition.effect === 'health_regeneration') totals.regen += value;
            if (definition.effect === 'pickup_range_bonus') totals.pickup += value;
            if (definition.effect === 'experience_bonus') totals.xp += value;
        }
        this.bonuses = totals;
        this.damageMultiplier = 1 + this.base.damageBonus + totals.damage;
        this.attackSpeedMultiplier = 1 + this.base.attackSpeedBonus + totals.speed;
        this.moveSpeed = 175 * (1 + this.base.movementSpeedBonus + totals.move);
        this.criticalChance = Math.min(0.75, this.base.criticalChanceBonus + totals.crit);
        this.regeneration = this.base.healthRegeneration + totals.regen;
        this.pickupRadius = 90 * (1 + totals.pickup);
    }

    attachDefinitions(definitions) {
        this.definitions = definitions;
        this.recalculate();
    }
}
