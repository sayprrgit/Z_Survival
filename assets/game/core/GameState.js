import { Player } from '../entities/Player.js';

export class GameState {
    constructor(configuration) {
        this.configuration = configuration;
        this.definitions = {
            weaponsByCode: new Map(configuration.weapons.map((item) => [item.code, item])),
            powerUpsByCode: new Map(configuration.powerUps.map((item) => [item.code, item])),
            enemiesByCode: new Map((configuration.enemies ?? []).map((item) => [item.code, item])),
        };
        const bosses = [...this.definitions.enemiesByCode.values()]
            .filter((enemy) => enemy.boss)
            .sort((first, second) => first.availableAt - second.availableAt);
        this.player = new Player(configuration);
        this.player.attachDefinitions(this.definitions);
        this.enemies = [];
        this.nextEntityId = 1;
        this.projectiles = [];
        this.orbs = [];
        this.fireZones = [];
        this.messages = [];
        this.pendingUpgrades = 0;
        this.elapsed = 0;
        this.kills = 0;
        this.spawnTimer = 0;
        this.wave = 1;
        this.bossesSpawned = new Set();
        this.finalBossCode = bosses.at(-1)?.code ?? null;
        this.finalBossAvailableAt = bosses.at(-1)?.availableAt ?? 0;
        this.objective = this.finalBossCode
            ? `Tenir jusqu'à ${Math.floor(this.finalBossAvailableAt / 60)}:${String(this.finalBossAvailableAt % 60).padStart(2, '0')}.`
            : 'Éliminer les infectés.';
        this.result = null;
        this.pauseReasons = new Set();
        this.over = false;
    }

    get paused() { return this.pauseReasons.size > 0; }
    pause(reason) { this.pauseReasons.add(reason); }
    resume(reason) { this.pauseReasons.delete(reason); }
    togglePause() { this.paused ? this.resume('manual') : this.pause('manual'); }
}
