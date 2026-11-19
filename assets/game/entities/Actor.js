export class Actor {
    constructor(x, y, radius, health) {
        Object.assign(this, { x, y, radius, health, maxHealth: health, dead: false });
    }

    takeDamage(amount) {
        if (this.dead) return;
        this.health = Math.max(0, this.health - amount);
        if (this.health === 0) this.dead = true;
    }
}
