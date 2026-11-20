import { Actor } from './Actor.js';

export class Enemy extends Actor {
    constructor(x, y, definition, scaling = 1) {
        const health = Math.max(1, Math.ceil(definition.health * scaling));
        super(x, y, definition.boss ? 28 : Math.max(11, Math.min(20, 10 + health / 20)), health);
        this.code = definition.code;
        this.name = definition.name;
        this.isBoss = Boolean(definition.boss);
        this.speed = definition.speed * (this.isBoss ? 1 : Math.min(1.3, 1 + (scaling - 1) * 0.15));
        this.damage = definition.damage * Math.min(2.4, scaling);
        this.attackRange = definition.range;
        this.attackInterval = definition.interval;
        this.experience = definition.experience;
        this.color = {
            walker: '#7e9c69', runner: '#d08b4f', brute: '#865c66', spitter: '#aa6d87',
            colossus: '#c74943', patient_zero: '#f1a65b',
        }[definition.code] ?? '#9aa173';
        this.hitCooldown = 0;
    }
}
