import { ExperienceOrb } from '../entities/ExperienceOrb.js';

export class LootSystem {
    rewardDefeatedEnemies(state) {
        const defeated = [];
        for (const enemy of state.enemies) {
            if (!enemy.dead || enemy.rewarded) continue;
            enemy.rewarded = true;
            defeated.push(enemy);
            state.kills++;
            state.orbs.push(new ExperienceOrb(enemy.x, enemy.y, enemy.experience, enemy.isBoss));
        }
        state.enemies = state.enemies.filter((enemy) => !enemy.dead);
        return defeated;
    }

    updateOrbs(state, dt) {
        const player = state.player;
        for (const orb of state.orbs) {
            const dx = player.x - orb.x;
            const dy = player.y - orb.y;
            const distanceSquared = dx * dx + dy * dy;
            const pickupDistance = player.pickupRadius + orb.radius;
            if (distanceSquared <= pickupDistance * pickupDistance) {
                const distance = Math.sqrt(distanceSquared) || 1;
                const speed = Math.min(380, (player.pickupRadius - distance + 60) * 5);
                const travel = Math.min(distance, speed * dt);
                orb.x += dx / distance * travel;
                orb.y += dy / distance * travel;
            }
            const collectDistance = player.radius + orb.radius + 4;
            const remainingX = player.x - orb.x;
            const remainingY = player.y - orb.y;
            if (remainingX * remainingX + remainingY * remainingY <= collectDistance * collectDistance) {
                player.xp += orb.experience * (1 + player.bonuses.xp);
                orb.collected = true;
            }
        }
        state.orbs = state.orbs.filter((orb) => !orb.collected);
    }
}
