import { Player } from '../entities/Player.js';

export class GameState {
    constructor(configuration) {
        this.configuration = configuration;
        this.definitions = {
            weaponsByCode: new Map(configuration.weapons.map((item) => [item.code, item])),
            powerUpsByCode: new Map(configuration.powerUps.map((item) => [item.code, item])),
        };
        this.player = new Player(configuration);
        this.player.attachDefinitions(this.definitions);
        this.enemies = [];
        this.projectiles = [];
        this.orbs = [];
        this.fireZones = [];
        this.elapsed = 0;
        this.kills = 0;
        this.spawnTimer = 0;
        this.paused = false;
        this.over = false;
    }
}
