import { Projectile } from '../entities/Projectile.js';

export class CombatSystem {
    update(state, dt, firing = false) {
        const player = state.player;
        this.updateBleed(state.enemies, dt);
        if (!firing) return;
        const item = player.activeWeapon;
        const weapon = state.definitions.weaponsByCode.get(player.activeWeaponCode);
        if (!item || !weapon || item.reload > 0 || item.cooldown > 0) return;
        if (item.ammo <= 0) { player.reloadWeapon(); return; }

        const aim = player.aim ?? player.facing;
        const angle = Math.atan2(aim.y, aim.x);
        const damage = weapon.damage * player.damageMultiplier * (1 + (item.level - 1) * .12) * (Math.random() < player.criticalChance ? 2 : 1);
        const interval = Math.max(.12, weapon.interval * Math.max(.62, 1 - (item.level - 1) * .07) / Math.max(.35, player.attackSpeedMultiplier));
        item.cooldown = interval;
        player.attackFlash = .12;
        state.soundEvents.push({ type: 'shot', weapon: player.activeWeaponCode });
        if (Number.isFinite(item.ammo)) item.ammo--;

        if (weapon.attackType === 'melee_area') {
            const radius = weapon.area * (1 + (item.level - 1) * .14);
            for (const enemy of state.enemies) if (!enemy.dead && this.inFront(player, enemy, aim, radius, .7)) enemy.takeDamage(damage);
        } else if (weapon.attackType === 'melee') {
            for (const enemy of state.enemies) if (!enemy.dead && this.inFront(player, enemy, aim, weapon.range + 22, .78)) {
                enemy.takeDamage(damage);
                if (item.level >= weapon.maxLevel) enemy.bleed = 2;
            }
        } else if (weapon.attackType === 'area_damage') {
            const target = this.aimTarget(player, weapon.range, aim);
            const radius = weapon.area * (1 + (item.level - 1) * .16);
            const projectile = new Projectile(player.x, player.y, aim.x * (player.activeWeaponCode === 'rocket_launcher' ? 420 : 260), aim.y * (player.activeWeaponCode === 'rocket_launcher' ? 420 : 260), damage, 8, 0, player.activeWeaponCode === 'rocket_launcher' ? '#ff8a45' : '#ef713f');
            projectile.impact = target; projectile.explosive = { radius, damage: damage * .45 }; state.projectiles.push(projectile);
        } else {
            const pelletCount = weapon.attackType === 'spread' ? Math.max(5, weapon.projectileCount) + (item.level >= 3 ? 2 : 0) : weapon.projectileCount + (item.level >= weapon.maxLevel ? 1 : 0);
            const cone = weapon.attackType === 'spread' ? Math.PI / 4 : .025;
            for (let index = 0; index < pelletCount; index++) {
                const fraction = pelletCount === 1 ? 0 : index / (pelletCount - 1) - .5;
                const direction = angle + fraction * cone;
                this.fireHitscan(state, player, Math.cos(direction), Math.sin(direction), 100000, damage, weapon.pierce + (item.level >= weapon.maxLevel ? 2 : 0), weapon.attackType === 'rapid' ? '#b9d77a' : '#ffd27a', player.activeWeaponCode === 'sniper');
            }
        }
    }

    fireHitscan(state, player, x, y, range, damage, pierce, color, piercesVisual) {
        const hits = state.enemies.filter((enemy) => !enemy.dead).map((enemy) => { const dx = enemy.x - player.x; const dy = enemy.y - player.y; const distance = dx * x + dy * y; return { enemy, distance, lateral: Math.abs(dx * y - dy * x) }; }).filter((hit) => hit.distance >= 0 && hit.distance <= range && hit.lateral <= hit.enemy.radius + 15).sort((a, b) => a.distance - b.distance);
        for (const hit of hits.slice(0, pierce + 1)) hit.enemy.takeDamage(damage);
        const visibleDistance = piercesVisual ? 1500 : Math.min(1500, hits[0]?.distance ?? 1500);
        const bullet = new Projectile(player.x, player.y, x * 2800, y * 2800, 0, 2.4, 0, color); bullet.visualOnly = true; bullet.lifetime = visibleDistance / 2800; state.projectiles.push(bullet);
    }

    aimTarget(player, range, aim) {
        const target = player.aimTarget ?? { x: player.x + aim.x * range, y: player.y + aim.y * range };
        const dx = target.x - player.x;
        const dy = target.y - player.y;
        const distance = Math.hypot(dx, dy) || 1;
        const limited = Math.min(range, distance);
        return { x: player.x + dx / distance * limited, y: player.y + dy / distance * limited };
    }

    inFront(player, enemy, aim, range, coneCosine) {
        const dx = enemy.x - player.x;
        const dy = enemy.y - player.y;
        const distance = Math.hypot(dx, dy) || 1;
        return distance <= range && (dx * aim.x + dy * aim.y) / distance >= coneCosine;
    }

    updateBleed(enemies, dt) { for (const enemy of enemies) if (enemy.bleed > 0) { enemy.bleed -= dt; enemy.takeDamage(dt * 4); } }
}
