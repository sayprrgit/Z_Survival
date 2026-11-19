import { Projectile } from '../entities/Projectile.js';

export class CombatSystem {
    update(state, dt) {
        const player = state.player;
        for (const [code, item] of player.weapons) {
            const weapon = state.definitions.weaponsByCode.get(code);
            item.cooldown -= dt;
            if (item.cooldown > 0 || !state.enemies.length) continue;
            const targets = state.enemies.filter((enemy) => !enemy.dead).map((enemy) => ({ enemy, distance: Math.hypot(enemy.x - player.x, enemy.y - player.y) })).filter(({ distance }) => distance <= weapon.range).sort((a, b) => a.distance - b.distance);
            if (!targets.length) continue;
            const target = targets[0].enemy;
            const angle = Math.atan2(target.y - player.y, target.x - player.x);
            const damage = weapon.damage * player.damageMultiplier * (1 + (item.level - 1) * 0.12) * (Math.random() < player.criticalChance ? 2 : 1);
            const interval = Math.max(0.12, weapon.interval * Math.max(0.62, 1 - (item.level - 1) * 0.07) / Math.max(0.35, player.attackSpeedMultiplier));
            item.cooldown = interval;
            if (weapon.attackType === 'melee_area') {
                const radius = weapon.area * (1 + (item.level - 1) * 0.14);
                for (const enemy of state.enemies) if (Math.hypot(enemy.x - player.x, enemy.y - player.y) <= radius) enemy.takeDamage(damage);
            } else if (weapon.attackType === 'melee') {
                for (const { enemy, distance } of targets.slice(0, 2)) {
                    if (distance < weapon.range + 22 && Math.abs(Math.atan2(Math.sin(Math.atan2(enemy.y-player.y, enemy.x-player.x)-angle), Math.cos(Math.atan2(enemy.y-player.y, enemy.x-player.x)-angle))) < 0.85) {
                        enemy.takeDamage(damage);
                        if (item.level >= weapon.maxLevel) enemy.bleed = 2;
                    }
                }
            } else if (weapon.attackType === 'area_damage') {
                const radius = weapon.area * (1 + (item.level - 1) * 0.16);
                state.fireZones.push({ x: target.x, y: target.y, radius, damage: damage * 0.45, duration: 3.5, tick: 0 });
            } else {
                const count = weapon.projectileCount + (weapon.attackType === 'spread' && item.level >= 3 ? 2 : 0) + (weapon.attackType === 'targeted' && item.level >= 5 ? 1 : 0);
                const spread = weapon.attackType === 'spread' ? 0.56 : 0.025;
                for (let index = 0; index < count; index++) {
                    const direction = angle + (index - (count - 1) / 2) * spread;
                    state.projectiles.push(new Projectile(player.x, player.y, Math.cos(direction) * 520, Math.sin(direction) * 520, damage, weapon.attackType === 'spread' ? 4 : 5, weapon.pierce + (item.level >= weapon.maxLevel ? 2 : 0), weapon.attackType === 'rapid' ? '#b9d77a' : '#ffd27a'));
                }
            }
        }

        for (const enemy of state.enemies) {
            if (enemy.bleed > 0) { enemy.bleed -= dt; enemy.takeDamage(dt * 4); }
        }
        for (const zone of state.fireZones) {
            zone.duration -= dt; zone.tick -= dt;
            if (zone.tick <= 0) {
                zone.tick = 0.45;
                for (const enemy of state.enemies) if (Math.hypot(enemy.x-zone.x, enemy.y-zone.y) < zone.radius) enemy.takeDamage(zone.damage);
            }
        }
        state.fireZones = state.fireZones.filter((zone) => zone.duration > 0);
    }
}
