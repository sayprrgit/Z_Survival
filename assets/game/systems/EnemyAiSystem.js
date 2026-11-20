export class EnemyAiSystem {
    update(state, dt) {
        const player = state.player;
        for (const enemy of state.enemies) {
            if (enemy.dead) continue;
            const dx = player.x - enemy.x;
            const dy = player.y - enemy.y;
            const distanceSquared = dx * dx + dy * dy;
            const attackDistance = player.radius + enemy.radius + enemy.attackRange;
            if (distanceSquared > attackDistance * attackDistance) {
                const distance = Math.sqrt(distanceSquared) || 1;
                enemy.x += dx / distance * enemy.speed * dt;
                enemy.y += dy / distance * enemy.speed * dt;
            }
            enemy.hitCooldown -= dt;
            enemy.attackFlash = Math.max(0, (enemy.attackFlash ?? 0) - dt);
            if (distanceSquared <= attackDistance * attackDistance && enemy.hitCooldown <= 0) {
                const damage = enemy.damage * (enemy.isBoss ? enemy.enrage ?? 1 : 1);
                player.takeDamage(damage * (1 - Math.min(0.8, player.base.damageReduction)));
                enemy.hitCooldown = enemy.attackInterval;
                enemy.attackFlash = 0.12;
            }
        }
    }
}
