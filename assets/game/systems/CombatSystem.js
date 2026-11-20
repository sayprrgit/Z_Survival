import { Projectile } from '../entities/Projectile.js';

export class CombatSystem {
    update(state, dt) {
        const player = state.player;
        const code = player.activeWeaponCode;
        const item = player.activeWeapon;
        const weapon = state.definitions.weaponsByCode.get(code);
        if (!item || !weapon || item.reload > 0 || item.cooldown > 0 || !state.enemies.length) return;
        if (item.ammo <= 0) { player.reloadWeapon(); return; }
        const targets = state.enemies.filter((enemy) => !enemy.dead).map((enemy) => {
                const dx = enemy.x - player.x;
                const dy = enemy.y - player.y;
                return { enemy, distanceSquared: dx * dx + dy * dy };
            }).filter(({ distanceSquared }) => distanceSquared <= weapon.range * weapon.range).sort((a, b) => a.distanceSquared - b.distanceSquared);
        if (!targets.length) return;
            const target = targets[0].enemy;
            const aimLength = Math.hypot(target.x - player.x, target.y - player.y) || 1;
            player.aim = { x: (target.x - player.x) / aimLength, y: (target.y - player.y) / aimLength };
            const angle = Math.atan2(target.y - player.y, target.x - player.x);
            const damage = weapon.damage * player.damageMultiplier * (1 + (item.level - 1) * 0.12) * (Math.random() < player.criticalChance ? 2 : 1);
            const interval = Math.max(0.12, weapon.interval * Math.max(0.62, 1 - (item.level - 1) * 0.07) / Math.max(0.35, player.attackSpeedMultiplier));
        item.cooldown = interval;
        if (Number.isFinite(item.ammo)) item.ammo--;
            if (weapon.attackType === 'melee_area') {
                const radius = weapon.area * (1 + (item.level - 1) * 0.14);
                for (const enemy of state.enemies) {
                    const dx = enemy.x - player.x;
                    const dy = enemy.y - player.y;
                    if (dx * dx + dy * dy <= radius * radius) enemy.takeDamage(damage);
                }
            } else if (weapon.attackType === 'melee') {
                for (const { enemy, distanceSquared } of targets.slice(0, 2)) {
                    if (distanceSquared < (weapon.range + 22) ** 2 && Math.abs(Math.atan2(Math.sin(Math.atan2(enemy.y-player.y, enemy.x-player.x)-angle), Math.cos(Math.atan2(enemy.y-player.y, enemy.x-player.x)-angle))) < 0.85) {
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
        for (const enemy of state.enemies) {
            if (enemy.bleed > 0) { enemy.bleed -= dt; enemy.takeDamage(dt * 4); }
        }
    }
}
