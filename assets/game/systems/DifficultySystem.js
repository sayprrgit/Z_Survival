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
        return 1 + Math.floor(elapsed / 45);
    }

    enemyScaleAt(elapsed) {
        return Math.min(2.5, (1 + elapsed / 720) * this.multiplier);
    }

    spawnIntervalAt(elapsed) {
        return Math.max(0.35, 1.25 / (1 + elapsed / 300) / this.multiplier);
    }
}
