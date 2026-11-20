import { LootChest } from '../entities/LootChest.js';

export class LootSystem {
    rewardDefeatedEnemies(state) {
        const defeated = [];
        for (const enemy of state.enemies) {
            if (!enemy.dead || enemy.rewarded) continue;
            enemy.rewarded = true;
            defeated.push(enemy);
            state.kills++;
            if (enemy.isBoss || Math.random() < .08) state.drops.push({ x: enemy.x, y: enemy.y, radius: 13, type: Math.random() < .25 ? 'weapon' : 'upgrade' });
            else if (Math.random() < .25) state.drops.push({ x: enemy.x, y: enemy.y, radius: 11, type: 'ammo', amount: 10 });
        }
        state.enemies = state.enemies.filter((enemy) => !enemy.dead);
        return defeated;
    }

    spawnWaveChests(state, wave) {
        const angle = Math.random() * Math.PI * 2;
        const distance = 210 + Math.random() * 130;
        state.chests.push(new LootChest(state.player.x + Math.cos(angle) * distance, state.player.y + Math.sin(angle) * distance, 'upgrade'));
        if (wave > 1) {
            const ammoAngle = angle + Math.PI * .8;
            state.chests.push(new LootChest(state.player.x + Math.cos(ammoAngle) * distance, state.player.y + Math.sin(ammoAngle) * distance, 'ammo'));
        }
    }

    interact(state) {
        const player = state.player;
        const closest = state.chests.filter((chest) => !chest.opened).sort((first, second) => this.distanceSquared(player, first) - this.distanceSquared(player, second))[0];
        if (!closest || this.distanceSquared(player, closest) > 58 ** 2) return null;
        if ('opened' in closest) { closest.opened = true; closest.openedAt = state.elapsed; }
        else closest.collected = true;
        if (closest.type === 'ammo') { player.addAmmo(closest.amount ?? 18); return 'ammo'; }
        if (closest.type === 'weapon') {
            const candidates = [...state.definitions.weaponsByCode.keys()].filter((code) => player.unlockedWeapons.has(code) && !player.weapons.has(code));
            if (candidates.length) player.addWeapon(candidates[Math.floor(Math.random() * candidates.length)]);
            else player.addAmmo(20);
            return 'weapon';
        }
        if (this.claimUpgrade(state)) return 'upgrade';
        player.addAmmo(10);
        return 'ammo';
    }

    update(state) {
        for (const drop of state.drops) {
            if (drop.collected || this.distanceSquared(state.player, drop) > (state.player.pickupRadius ?? 58) ** 2) continue;
            drop.collected = true;
            if (drop.type === 'ammo') { state.player.addAmmo(drop.amount ?? 8); state.soundEvents.push({ type: 'loot' }); }
            else if (drop.type === 'weapon') {
                const candidates = [...state.definitions.weaponsByCode.keys()].filter((code) => state.player.unlockedWeapons.has(code) && !state.player.weapons.has(code));
                if (candidates.length) state.player.addWeapon(candidates[Math.floor(Math.random() * candidates.length)]);
                else state.player.addAmmo(20);
            } else if (this.claimUpgrade(state)) state.pendingUpgradeDrops++;
            else state.player.addAmmo(6);
        }
        state.chests = state.chests.filter((chest) => !chest.opened || state.elapsed - chest.openedAt < .55);
        state.drops = state.drops.filter((drop) => !drop.collected);
    }

    distanceSquared(first, second) { const dx = first.x - second.x; const dy = first.y - second.y; return dx * dx + dy * dy; }
    claimUpgrade(state) { if (state.upgradesThisWave >= 1) return false; state.upgradesThisWave++; return true; }
}
