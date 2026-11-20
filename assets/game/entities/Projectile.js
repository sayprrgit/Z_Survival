export class Projectile {
    constructor(x, y, vx, vy, damage, radius = 5, pierce = 0, color = '#ffd27a') {
        Object.assign(this, { x, y, originX: x, originY: y, vx, vy, damage, radius, pierce, color, dead: false, lifetime: 1.8 });
        this.hit = new Set();
    }
}
