import { Actor } from './Actor.js';

export class Enemy extends Actor {
    constructor(x, y, level = 1) {
        const health = 28 + level * 3;
        super(x, y, 12 + Math.min(level, 8) * 0.6, health);
        this.speed = 32 + Math.random() * 18 + level * 0.25;
        this.damage = 8 + Math.floor(level / 3);
        this.color = Math.random() > 0.82 ? '#c77a42' : '#7e9c69';
        this.hitCooldown = 0;
    }
}
