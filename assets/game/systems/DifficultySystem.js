export class DifficultySystem {
    constructor(definitions = [], selectedCode = 'normal') {
        this.definition = definitions.find((difficulty) => difficulty.code === selectedCode)
            ?? definitions[0]
            ?? { code: 'normal', name: 'Normal', multiplier: 1 };
    }

    get multiplier() {
        return this.definition.multiplier;
    }

    waveAt(elapsed) {
        return 1 + Math.floor(elapsed / 30);
    }

    enemyScaleAt(elapsed) {
        return Math.min(3, (1 + elapsed / 500) * this.multiplier);
    }

    spawnIntervalAt(elapsed) {
        return Math.max(0.2, 0.95 / (1 + elapsed / 210) / this.multiplier);
    }
}
