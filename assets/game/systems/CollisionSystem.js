export class CollisionSystem {
    constructor(cellSize = 96) { this.cellSize = cellSize; this.cells = new Map(); }

    update(state, dt) {
        this.indexEnemies(state.enemies);
        this.updateProjectiles(state, dt);
        this.updateFireZones(state, dt);
    }

    indexEnemies(enemies) {
        this.cells.clear();
        for (const enemy of enemies) {
            if (enemy.dead) continue;
            const key = this.key(enemy.x, enemy.y);
            const cell = this.cells.get(key) ?? [];
            cell.push(enemy);
            this.cells.set(key, cell);
        }
    }

    updateProjectiles(state, dt) {
        for (const projectile of state.projectiles) {
            if (projectile.dead) continue;
            projectile.x += projectile.vx * dt;
            projectile.y += projectile.vy * dt;
            projectile.lifetime -= dt;
            if (projectile.lifetime <= 0) { projectile.dead = true; continue; }
            for (const enemy of this.nearby(projectile.x, projectile.y)) {
                if (enemy.dead || projectile.hit.has(enemy)) continue;
                const radius = enemy.radius + projectile.radius;
                const dx = enemy.x - projectile.x;
                const dy = enemy.y - projectile.y;
                if (dx * dx + dy * dy > radius * radius) continue;
                enemy.takeDamage(projectile.damage);
                projectile.hit.add(enemy);
                if (projectile.pierce-- <= 0) { projectile.dead = true; break; }
            }
        }
        state.projectiles = state.projectiles.filter((projectile) => !projectile.dead);
    }

    updateFireZones(state, dt) {
        for (const zone of state.fireZones) {
            zone.duration -= dt;
            zone.tick -= dt;
            if (zone.tick > 0) continue;
            zone.tick = 0.45;
            const radiusSquared = zone.radius * zone.radius;
            for (const enemy of this.nearby(zone.x, zone.y, zone.radius)) {
                const dx = enemy.x - zone.x;
                const dy = enemy.y - zone.y;
                if (!enemy.dead && dx * dx + dy * dy <= radiusSquared) enemy.takeDamage(zone.damage);
            }
        }
        state.fireZones = state.fireZones.filter((zone) => zone.duration > 0);
    }

    nearby(x, y, radius = this.cellSize) {
        const cellRadius = Math.ceil(radius / this.cellSize);
        const originX = Math.floor(x / this.cellSize);
        const originY = Math.floor(y / this.cellSize);
        const results = [];
        for (let offsetX = -cellRadius; offsetX <= cellRadius; offsetX++) for (let offsetY = -cellRadius; offsetY <= cellRadius; offsetY++) results.push(...(this.cells.get(`${originX + offsetX}:${originY + offsetY}`) ?? []));
        return results;
    }

    key(x, y) { return `${Math.floor(x / this.cellSize)}:${Math.floor(y / this.cellSize)}`; }
}
