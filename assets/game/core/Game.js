import { GameLoop } from './GameLoop.js';
import { GameState } from './GameState.js';
import { InputManager } from './InputManager.js';
import { GameApi } from '../api/GameApi.js';
import { CombatSystem } from '../systems/CombatSystem.js';
import { CollisionSystem } from '../systems/CollisionSystem.js';
import { DifficultySystem } from '../systems/DifficultySystem.js';
import { EnemyAiSystem } from '../systems/EnemyAiSystem.js';
import { LootSystem } from '../systems/LootSystem.js';
import { MovementSystem } from '../systems/MovementSystem.js';
import { SpawnSystem } from '../systems/SpawnSystem.js';
import { UpgradeSystem } from '../systems/UpgradeSystem.js';
import { Camera } from '../rendering/Camera.js';
import { Renderer } from '../rendering/Renderer.js';
import { ThreeRenderer } from '../rendering/ThreeRenderer.js';

export class Game {
    constructor(root, configuration) {
        this.root = root;
        this.state = new GameState(configuration);
        this.api = new GameApi(root);
        this.runId = null;
        this.input = new InputManager();
        this.difficulty = new DifficultySystem(configuration.difficulties, configuration.difficulty);
        this.movement = new MovementSystem();
        this.enemyAi = new EnemyAiSystem();
        this.combat = new CombatSystem();
        this.collision = new CollisionSystem();
        this.loot = new LootSystem();
        this.spawner = new SpawnSystem(this.state, this.difficulty);
        this.upgrades = new UpgradeSystem(this.state, root);
        this.renderer = this.createRenderer(root.querySelector('[data-game-canvas]'));
        this.loop = new GameLoop((dt) => this.update(dt), () => this.renderer.render(this.state));
        this.pauseButton = root.querySelector('[data-game-pause]');
        this.fullscreenButton = root.querySelector('[data-game-fullscreen]');
        this.resize = () => this.renderer.resize();
        this.visibilityChange = () => document.hidden ? this.state.pause('visibility') : this.state.resume('visibility');
        this.fullscreenChange = () => { this.renderer.resize(); this.updateFullscreenButton(); };
        this.pauseButton?.addEventListener('click', () => this.togglePause());
        this.fullscreenButton?.addEventListener('click', () => this.toggleFullscreen());
        window.addEventListener('resize', this.resize);
        document.addEventListener('visibilitychange', this.visibilityChange);
        document.addEventListener('fullscreenchange', this.fullscreenChange);
        this.renderer.resize();
        this.updateHud();
        this.initializeRun();
    }

    createRenderer(canvas) {
        try {
            const renderer = new ThreeRenderer(canvas);
            canvas.dataset.renderer = 'webgl';
            return renderer;
        } catch (error) {
            console.warn('WebGL indisponible, activation du rendu isométrique.', error);
            const fallback = canvas.cloneNode(false);
            canvas.replaceWith(fallback);
            fallback.dataset.renderer = 'canvas';

            return new Renderer(fallback, new Camera());
        }
    }

    async initializeRun() {
        const status = this.root.querySelector('[data-run-status]');

        try {
            const run = await this.api.startRun({
                character: this.state.configuration.player.code,
                difficulty: this.state.configuration.difficulty,
            });
            this.runId = run.id;
            if (status) status.hidden = true;
            this.loop.start();
        } catch (error) {
            if (status) {
                status.textContent = `Impossible de démarrer la partie : ${error.message}`;
                status.classList.add('is-error');
            }
        }
    }

    update(dt) {
        const state = this.state;
        if (this.input.consume('pause') && !state.over && !this.upgrades.active) this.togglePause();
        if (this.input.consume('fullscreen')) this.toggleFullscreen();
        if (state.over || state.paused) {
            this.updateHud();

            return;
        }

        state.elapsed += dt;
        this.movement.update(state.player, this.input.vector(), dt);
        state.player.health = Math.min(state.player.maxHealth, state.player.health + state.player.regeneration * dt);
        this.spawner.update(dt, this.renderer.width, this.renderer.height);
        this.enemyAi.update(state, dt);
        this.combat.update(state, dt);
        this.collision.update(state, dt);
        this.handleDefeatedEnemies(this.loot.rewardDefeatedEnemies(state));
        if (state.over) {
            this.updateHud();

            return;
        }

        this.loot.updateOrbs(state, dt);
        if (state.player.dead) this.end('defeat');
        else this.resolveLevels();
        this.updateHud();
    }

    handleDefeatedEnemies(defeated) {
        for (const enemy of defeated) {
            if (enemy.code === this.state.finalBossCode) this.end('victory');
            else if (enemy.isBoss) this.state.objective = `Boss neutralisé. Tiens jusqu'à ${this.formatTime(this.state.finalBossAvailableAt)}.`;
        }
    }

    resolveLevels() {
        const player = this.state.player;
        while (player.xp >= player.xpToNext) {
            player.xp -= player.xpToNext;
            player.level++;
            player.xpToNext = Math.floor(5 + player.level * 2.3);
            this.state.pendingUpgrades++;
        }
        if (this.state.pendingUpgrades > 0 && !this.upgrades.active) {
            this.state.pendingUpgrades--;
            this.upgrades.open();
        }
    }

    togglePause() {
        if (this.state.pauseReasons.has('manual')) this.state.resume('manual');
        else this.state.pause('manual');
        this.updateHud();
    }

    async toggleFullscreen() {
        try {
            if (document.fullscreenElement === this.root) await document.exitFullscreen();
            else await this.root.requestFullscreen();
        } catch {
            this.setText('[data-objective]', 'Le plein écran n’est pas disponible dans ce navigateur.');
        }
    }

    updateHud() {
        const { player, elapsed, kills, wave, objective, paused, over } = this.state;
        this.setText('[data-level]', player.level);
        this.setText('[data-kills]', kills);
        this.setText('[data-health]', Math.ceil(player.health));
        this.setText('[data-wave]', wave);
        this.setText('[data-objective]', objective);
        this.setText('[data-time]', this.formatTime(elapsed));
        const healthBar = this.root.querySelector('[data-health-bar]');
        if (healthBar) healthBar.style.width = `${100 * player.health / player.maxHealth}%`;
        if (this.pauseButton) {
            this.pauseButton.textContent = paused && !over ? 'Reprendre' : 'Pause';
            this.pauseButton.setAttribute('aria-pressed', String(paused && !over));
        }
        this.updateFullscreenButton();
    }

    end(result) {
        if (this.state.over) return;
        this.state.over = true;
        this.state.result = result;
        this.state.pause('end');
        const panel = this.root.querySelector('[data-game-over]');
        if (panel) panel.hidden = false;
        this.setText('[data-end-kicker]', result === 'victory' ? 'MISSION RÉUSSIE' : 'SURVIVANT PERDU');
        this.setText('[data-end-title]', result === 'victory' ? 'Zone sécurisée' : 'Fin de partie');
        const label = result === 'victory' ? 'Boss final neutralisé' : 'Survie';
        this.setText('[data-end-stats]', `${label} : ${this.formatTime(this.state.elapsed)} · Éliminations : ${this.state.kills} · Niveau ${this.state.player.level}`);
        this.updateHud();

        if (this.runId === null) return;

        const resultPayload = {
            survivalSeconds: Math.floor(this.state.elapsed),
            reachedLevel: this.state.player.level,
            kills: this.state.kills,
            result,
        };
        if (result === 'victory') resultPayload.bossCode = this.state.finalBossCode;

        this.api.finishRun(this.runId, resultPayload).then((summary) => {
            const stats = this.root.querySelector('[data-end-stats]');
            if (stats) stats.textContent += ` · +${summary.scrapEarned} ferraille (solde ${summary.scrapBalance})`;
        }).catch((error) => {
            const stats = this.root.querySelector('[data-end-stats]');
            if (stats) stats.textContent += ` · Récompense non enregistrée : ${error.message}`;
        });
    }

    setText(selector, value) {
        const node = this.root.querySelector(selector);
        if (node) node.textContent = value;
    }

    updateFullscreenButton() {
        if (!this.fullscreenButton) return;
        const active = document.fullscreenElement === this.root;
        this.fullscreenButton.textContent = active ? 'Quitter le plein écran' : 'Plein écran';
        this.fullscreenButton.setAttribute('aria-pressed', String(active));
    }

    formatTime(seconds) {
        const total = Math.floor(seconds);

        return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`;
    }

    destroy() {
        this.loop.stop();
        this.input.destroy();
        window.removeEventListener('resize', this.resize);
        document.removeEventListener('visibilitychange', this.visibilityChange);
        document.removeEventListener('fullscreenchange', this.fullscreenChange);
        this.renderer.destroy?.();
    }
}
