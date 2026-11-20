import { Enemy } from '../entities/Enemy.js';
import { Boss } from '../entities/Boss.js';

export class SpawnSystem {
    constructor(state, difficulty) {
        this.state = state;
        this.difficulty = difficulty;
    }

    update(dt, width, height) {
        const state = this.state;
        state.wave = this.difficulty.waveAt(state.elapsed);
        this.spawnAvailableBosses(width, height);
        state.spawnTimer -= dt;
        if (state.spawnTimer > 0) return;

        const available = [...state.definitions.enemiesByCode.values()]
            .filter((enemy) => !enemy.boss && enemy.availableAt <= state.elapsed && enemy.weight > 0);
        if (!available.length) return;

        const count = Math.min(6, 1 + Math.floor(state.wave / 3));
        for (let index = 0; index < count; index++) this.spawn(this.pickWeighted(available), width, height);
        state.spawnTimer = this.difficulty.spawnIntervalAt(state.elapsed);
    }

    spawnAvailableBosses(width, height) {
        for (const enemy of this.state.definitions.enemiesByCode.values()) {
            if (!enemy.boss || enemy.availableAt > this.state.elapsed || this.state.bossesSpawned.has(enemy.code)) continue;
            this.state.bossesSpawned.add(enemy.code);
            this.spawn(enemy, width, height, true);
            this.state.objective = enemy.code === this.state.finalBossCode
                ? `Élimine ${enemy.name} pour remporter la partie.`
                : `Boss détecté : élimine ${enemy.name}.`;
        }
    }

    spawn(definition, width, height, boss = false) {
        const angle = Math.random() * Math.PI * 2;
        const radius = Math.max(width, height) * (boss ? 0.82 : 0.65);
        const EnemyClass = boss ? Boss : Enemy;
        const enemy = new EnemyClass(
            this.state.player.x + Math.cos(angle) * radius,
            this.state.player.y + Math.sin(angle) * radius,
            definition,
            this.difficulty.enemyScaleAt(this.state.elapsed),
        );
        enemy.runtimeId = this.state.nextEntityId++;
        this.state.enemies.push(enemy);
    }

    pickWeighted(definitions) {
        const total = definitions.reduce((sum, enemy) => sum + enemy.weight, 0);
        let roll = Math.random() * total;
        for (const enemy of definitions) {
            roll -= enemy.weight;
            if (roll <= 0) return enemy;
        }

        return definitions.at(-1);
    }
}
