import { GameLoop } from './GameLoop.js';
import { GameState } from './GameState.js';
import { InputManager } from './InputManager.js';
import { CombatSystem } from '../systems/CombatSystem.js';
import { CollisionSystem } from '../systems/CollisionSystem.js';
import { DifficultySystem } from '../systems/DifficultySystem.js';
import { EnemyAiSystem } from '../systems/EnemyAiSystem.js';
import { LootSystem } from '../systems/LootSystem.js';
import { MovementSystem } from '../systems/MovementSystem.js';
import { SpawnSystem } from '../systems/SpawnSystem.js';
import { UpgradeSystem } from '../systems/UpgradeSystem.js';
import { TalentSystem } from '../systems/TalentSystem.js';
import { SoundSystem } from '../systems/SoundSystem.js';
import { Camera } from '../rendering/Camera.js';
import { Renderer } from '../rendering/Renderer.js';
import { ThreeRenderer } from '../rendering/ThreeRenderer.js';

export class Game {
    constructor(root, configuration) {
        this.root = root;
        this.state = new GameState(configuration);
        this.canvas = root.querySelector('[data-game-canvas]');
        this.difficulty = new DifficultySystem(configuration.difficulties, configuration.difficulty);
        this.movement = new MovementSystem();
        this.enemyAi = new EnemyAiSystem();
        this.combat = new CombatSystem();
        this.collision = new CollisionSystem();
        this.loot = new LootSystem();
        this.state.lootSystem = this.loot;
        this.spawner = new SpawnSystem(this.state, this.difficulty);
        this.upgrades = new UpgradeSystem(this.state, root);
        this.talents = new TalentSystem(this.state, root);
        this.sound = new SoundSystem();
        this.renderer = this.createRenderer(this.canvas);
        this.canvas = this.renderer.canvas;
        this.input = new InputManager(window, this.canvas);
        this.loop = new GameLoop((dt) => this.update(dt), () => this.renderer.render(this.state));
        this.pauseButton = root.querySelector('[data-game-pause]');
        this.fullscreenButton = root.querySelector('[data-game-fullscreen]');
        this.talentButtons = root.querySelectorAll('[data-open-talents]');
        this.hud = {
            kills: root.querySelector('[data-kills]'), health: root.querySelector('[data-health]'),
            wave: root.querySelector('[data-wave]'), objective: root.querySelector('[data-objective]'), time: root.querySelector('[data-time]'),
            healthBar: root.querySelector('[data-health-bar]'), weapons: root.querySelector('[data-weapons]'), augments: root.querySelector('[data-augments]'), ammo: root.querySelector('[data-ammo]'), activeWeapon: root.querySelector('[data-active-weapon]'), reload: root.querySelector('[data-reload-status]'),
        };
        this.selectWeaponFromHud = (event) => {
            const control = event.target.closest('[data-weapon-code]');
            if (!control || !this.hud.weapons?.contains(control)) return;
            this.state.player.selectWeapon(control.dataset.weaponCode);
            this.updateHud();
        };
        this.hud.weapons?.addEventListener('click', this.selectWeaponFromHud);
        this.nextHudUpdate = 0;
        this.resize = () => this.renderer.resize();
        this.visibilityChange = () => document.hidden ? this.state.pause('visibility') : this.state.resume('visibility');
        this.fullscreenChange = () => { this.renderer.resize(); this.updateFullscreenButton(); };
        this.pauseButton?.addEventListener('click', () => this.togglePause());
        this.fullscreenButton?.addEventListener('click', () => this.toggleFullscreen());
        this.talentButtons.forEach((button) => button.addEventListener('click', () => this.talents.active ? this.talents.close() : this.talents.open()));
        window.addEventListener('resize', this.resize);
        document.addEventListener('visibilitychange', this.visibilityChange);
        document.addEventListener('fullscreenchange', this.fullscreenChange);
        this.renderer.resize();
        this.updateHud();
        this.loop.start();
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

    update(dt) {
        const state = this.state;
        if (this.input.consume('pause') && !state.over && !this.upgrades.active) this.togglePause();
        if (this.input.consume('fullscreen')) this.toggleFullscreen();
        if (this.input.consume('switchWeapon')) state.player.switchWeapon();
        const weaponSwitchDirection = this.input.consumeWeaponSwitchDirection();
        if (weaponSwitchDirection) state.player.switchWeapon(weaponSwitchDirection);
        const weaponSlot = this.input.consumeWeaponSlot();
        if (weaponSlot !== null) state.player.selectWeapon([...state.player.weapons.keys()][weaponSlot]);
        if (this.input.consume('reload') && state.player.reloadWeapon()) state.soundEvents.push({ type: 'reload' });
        if (this.input.consume('interact')) {
            const loot = this.loot.interact(state);
            if (loot) state.soundEvents.push({ type: 'chest' });
            if (loot === 'upgrade') this.upgrades.open();
        }
        if (state.over || state.paused) { this.updateHud(); return; }

        state.elapsed += dt;
        this.updateAim();
        this.movement.update(state.player, this.input.vector(), dt, state.world);
        state.world.update(state.player);
        state.player.updateWeapons(dt);
        state.player.health = Math.min(state.player.maxHealth, state.player.health + state.player.regeneration * dt);
        this.spawner.update(dt, this.renderer.width, this.renderer.height);
        this.enemyAi.update(state, dt);
        this.combat.update(state, dt, this.input.isFiring());
        this.collision.update(state, dt);
        this.handleDefeatedEnemies(this.loot.rewardDefeatedEnemies(state));
        if (state.over) { this.updateHud(); return; }
        this.loot.update(state);
        this.sound.play(state.soundEvents);
        if (state.pendingUpgradeDrops > 0 && !this.upgrades.active) {
            state.pendingUpgradeDrops--;
            this.upgrades.open();
        }
        if (state.player.dead) this.end('defeat');
        if (state.elapsed >= this.nextHudUpdate) {
            this.nextHudUpdate = state.elapsed + .1;
            this.updateHud();
        }
    }

    updateAim() {
        const pointer = this.input.pointer();
        if (!pointer) { this.state.player.aimPreview = null; return; }
        const target = this.renderer.screenToWorld?.(pointer.x, pointer.y);
        if (!target) { this.state.player.aimPreview = null; return; }
        const dx = target.x - this.state.player.x;
        const dy = target.y - this.state.player.y;
        const length = Math.hypot(dx, dy);
        if (length < 1) return;
        this.state.player.aim = { x: dx / length, y: dy / length };
        this.state.player.aimTarget = target;
        this.updateAimPreview();
    }

    updateAimPreview() {
        const { player, enemies, definitions } = this.state;
        const weapon = definitions.weaponsByCode.get(player.activeWeaponCode);
        const range = weapon?.range ?? 260;
        const spread = weapon?.attackType === 'spread';
        let hit = null;
        let closest = range;
        for (const enemy of enemies) {
            if (enemy.dead) continue;
            const dx = enemy.x - player.x;
            const dy = enemy.y - player.y;
            const distance = dx * player.aim.x + dy * player.aim.y;
            if (distance < 0 || distance > closest) continue;
            const lateral = Math.abs(dx * player.aim.y - dy * player.aim.x);
            const width = enemy.radius + (spread ? distance * Math.tan(Math.PI / 8) : 8);
            if (lateral > width) continue;
            hit = enemy;
            closest = distance;
        }
        const distance = hit ? closest : range;
        player.aimPreview = { x: player.x + player.aim.x * distance, y: player.y + player.aim.y * distance, hit: Boolean(hit) };
    }

    handleDefeatedEnemies(defeated) {
        for (const enemy of defeated) {
            if (enemy.code === this.state.finalBossCode) this.end('victory');
            else if (enemy.isBoss) this.state.objective = `Boss neutralisé. Tiens jusqu'à ${this.formatTime(this.state.finalBossAvailableAt)}.`;
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
        this.setNodeText(this.hud.kills, kills);
        this.setText('[data-resources]', this.state.resources);
        this.setNodeText(this.hud.health, Math.ceil(player.health));
        this.setNodeText(this.hud.wave, wave);
        this.setNodeText(this.hud.objective, objective);
        this.setNodeText(this.hud.time, this.formatTime(elapsed));
        if (this.hud.healthBar) this.hud.healthBar.style.width = `${100 * player.health / player.maxHealth}%`;
        const activeWeapon = player.activeWeapon;
        const activeDefinition = this.state.definitions.weaponsByCode.get(player.activeWeaponCode);
        if (this.hud.activeWeapon) this.setNodeText(this.hud.activeWeapon, activeDefinition?.name ?? 'Arme');
        if (this.hud.ammo) this.setNodeText(this.hud.ammo, player.activeWeaponCode === 'minigun' ? String(activeWeapon?.ammo ?? 0) : Number.isFinite(activeWeapon?.ammo) ? `${activeWeapon.ammo} / ${activeWeapon.magazine}` : '∞');
        if (this.hud.reload) this.setNodeText(this.hud.reload, activeWeapon?.reload > 0 ? `RECHARGEMENT ${Math.ceil(activeWeapon.reload * 10) / 10}s` : Number.isFinite(activeWeapon?.reserve) ? `RÉSERVE ${activeWeapon.reserve}` : 'CORPS À CORPS');
        if (this.hud.weapons) this.hud.weapons.replaceChildren(...[...player.weapons].map(([code, item]) => {
            const slot = document.createElement('button');
            slot.type = 'button';
            slot.className = code === player.activeWeaponCode ? 'is-active' : '';
            slot.dataset.weaponCode = code;
            slot.textContent = `${[...player.weapons.keys()].indexOf(code) + 1} · ${this.state.definitions.weaponsByCode.get(code)?.name ?? code}`;
            return slot;
        }));
        if (this.hud.augments) this.hud.augments.replaceChildren(...[...player.powerUps].map(([code, item]) => {
            const augment = document.createElement('span');
            augment.textContent = `${this.state.definitions.powerUpsByCode.get(code)?.name ?? code} N${item.level}`;
            return augment;
        }));
        if (this.pauseButton) { this.pauseButton.textContent = paused && !over ? 'Reprendre' : 'Pause'; this.pauseButton.setAttribute('aria-pressed', String(paused && !over)); }
        this.updateFullscreenButton();
    }

    end(result) {
        if (this.state.over) return;
        this.state.over = true;
        this.state.result = result;
        this.state.pause('end');
        this.state.soundEvents.push({ type: result === 'victory' ? 'loot' : 'explosion' });
        this.sound.play(this.state.soundEvents);
        this.talents.save();
        const panel = this.root.querySelector('[data-game-over]');
        if (panel) panel.hidden = false;
        this.setText('[data-end-kicker]', result === 'victory' ? 'MISSION RÉUSSIE' : 'SURVIVANT PERDU');
        this.setText('[data-end-title]', result === 'victory' ? 'Zone sécurisée' : 'Fin de partie');
        const label = result === 'victory' ? 'Boss final neutralisé' : 'Survie';
        this.setText('[data-end-stats]', `${label} : ${this.formatTime(this.state.elapsed)} · Éliminations : ${this.state.kills} · Niveau ${this.state.player.level}`);
        this.updateHud();
    }

    setText(selector, value) { const node = this.root.querySelector(selector); if (node) node.textContent = value; }
    setNodeText(node, value) { if (node && node.textContent !== String(value)) node.textContent = value; }
    updateFullscreenButton() { if (this.fullscreenButton) { const active = document.fullscreenElement === this.root; this.fullscreenButton.textContent = active ? 'Quitter le plein écran' : 'Plein écran'; this.fullscreenButton.setAttribute('aria-pressed', String(active)); } }
    formatTime(seconds) { const total = Math.floor(seconds); return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`; }

    destroy() {
        this.loop.stop();
        this.input.destroy();
        window.removeEventListener('resize', this.resize);
        document.removeEventListener('visibilitychange', this.visibilityChange);
        document.removeEventListener('fullscreenchange', this.fullscreenChange);
        this.hud.weapons?.removeEventListener('click', this.selectWeaponFromHud);
        this.renderer.destroy?.();
    }
}
