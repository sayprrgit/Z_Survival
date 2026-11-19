import { AssetLoader } from './AssetLoader.js';
import { IsometricProjection } from './IsometricProjection.js';

const survivorCells = { mason: [0, 0], raven: [1, 0], boris: [0, 1], eva: [1, 1] };
const zombieCells = { walker: [0, 0], runner: [1, 0], brute: [2, 0], spitter: [0, 1], colossus: [1, 1], patient_zero: [2, 1] };

export class Renderer {
    constructor(canvas, camera) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.camera = camera;
        this.projection = new IsometricProjection();
        this.width = 0;
        this.height = 0;
        this.assets = new AssetLoader();
        this.assets.load('background', '/images/backgrounds/zombie-city-alert.png');
        this.assets.load('survivors', '/images/sprites/survivor-isometric-roster-v1.png');
        this.assets.load('zombies', '/images/sprites/zombie-isometric-roster-v1.png');
    }

    resize() {
        const box = this.canvas.getBoundingClientRect();
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        this.canvas.width = Math.floor(box.width * ratio);
        this.canvas.height = Math.floor(box.height * ratio);
        this.ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        this.width = box.width;
        this.height = box.height;
        this.camera.resize(this.width, this.height);
    }

    render(state) {
        this.camera.follow(state.player);
        const { ctx } = this;
        ctx.clearRect(0, 0, this.width, this.height);
        this.drawGround(state.elapsed);
        this.drawFireZones(state.fireZones);
        this.drawOrbs(state.orbs, state.elapsed);
        this.drawProjectiles(state.projectiles);
        this.drawActors(state);
        this.drawExperienceBar(state.player);
        if (state.paused && !state.over) this.drawPause();
    }

    project(entity) { return this.projection.project(entity.x, entity.y, this.camera); }

    drawGround(elapsed) {
        const { ctx } = this;
        ctx.fillStyle = '#0b0d0e'; ctx.fillRect(0, 0, this.width, this.height);
        const background = this.assets.get('background');
        if (background?.complete && background.naturalWidth) {
            const scale = Math.max(this.width / background.naturalWidth, this.height / background.naturalHeight);
            ctx.globalAlpha = .13;
            ctx.drawImage(background, (this.width - background.naturalWidth * scale) / 2, (this.height - background.naturalHeight * scale) / 2, background.naturalWidth * scale, background.naturalHeight * scale);
            ctx.globalAlpha = 1;
        }
        const tile = 76;
        const startX = Math.floor(this.camera.x / tile) - 10;
        const startY = Math.floor(this.camera.y / tile) - 10;
        for (let x = startX; x < startX + 21; x++) for (let y = startY; y < startY + 21; y++) {
            const point = this.projection.project(x * tile, y * tile, this.camera);
            const shade = (x + y) % 2 === 0 ? '#202526' : '#1b2021';
            ctx.fillStyle = shade;
            ctx.beginPath();
            ctx.moveTo(point.x, point.y - 19); ctx.lineTo(point.x + 38, point.y); ctx.lineTo(point.x, point.y + 19); ctx.lineTo(point.x - 38, point.y); ctx.closePath(); ctx.fill();
            ctx.strokeStyle = 'rgba(235,105,87,.075)'; ctx.stroke();
            if ((x * 17 + y * 31) % 13 === 0) { ctx.fillStyle = 'rgba(122,72,55,.33)'; ctx.fillRect(point.x - 2, point.y - 1, 4, 2); }
        }
        const glow = ctx.createRadialGradient(this.width / 2, this.height / 2, 30, this.width / 2, this.height / 2, Math.max(this.width, this.height) * .72);
        glow.addColorStop(0, 'rgba(0,0,0,0)'); glow.addColorStop(1, 'rgba(0,0,0,.7)'); ctx.fillStyle = glow; ctx.fillRect(0, 0, this.width, this.height);
        ctx.globalAlpha = .18 + Math.sin(elapsed * .6) * .03; ctx.fillStyle = '#d94a45'; ctx.fillRect(0, 0, this.width, 2); ctx.globalAlpha = 1;
    }

    drawActors(state) {
        const actors = state.enemies.map((enemy) => ({ entity: enemy, kind: 'enemy', at: this.project(enemy) }));
        actors.push({ entity: state.player, kind: 'player', at: this.project(state.player) });
        actors.sort((first, second) => first.at.y - second.at.y);
        for (const actor of actors) actor.kind === 'player' ? this.drawPlayer(actor.entity, actor.at, state.elapsed) : this.drawEnemy(actor.entity, actor.at, state.elapsed);
    }

    drawEnemy(enemy, at, elapsed) {
        const image = this.assets.get('zombies');
        const [column, row] = zombieCells[enemy.code] ?? zombieCells.walker;
        const bob = Math.sin(elapsed * (enemy.code === 'runner' ? 8 : 4) + enemy.x * .02) * (enemy.isBoss ? 3 : 1.8);
        const scale = enemy.isBoss ? 1.45 : enemy.code === 'brute' ? 1.15 : 1;
        this.drawShadow(at.x, at.y, enemy.radius * scale);
        if (enemy.isBoss) { this.ctx.strokeStyle = 'rgba(245,121,76,.7)'; this.ctx.lineWidth = 2; this.ctx.beginPath(); this.ctx.ellipse(at.x, at.y + 5, enemy.radius * 1.42, enemy.radius * .42, 0, 0, Math.PI * 2); this.ctx.stroke(); }
        this.drawSprite(image, column, row, 3, 2, at.x, at.y + bob, 82 * scale, 116 * scale);
        this.drawHealthBar(at, enemy);
    }

    drawPlayer(player, at, elapsed) {
        const image = this.assets.get('survivors');
        const [column, row] = survivorCells[player.base.code] ?? survivorCells.mason;
        const bob = Math.sin(elapsed * (player.isMoving ? 7 : 3)) * 1.6;
        this.drawShadow(at.x, at.y, 23);
        this.ctx.shadowColor = '#f5b65d'; this.ctx.shadowBlur = 16;
        this.drawSprite(image, column, row, 2, 2, at.x, at.y + bob, 88, 126);
        this.ctx.shadowBlur = 0;
        this.drawWeaponEffect(player, at, elapsed);
        this.ctx.strokeStyle = 'rgba(252,222,151,.72)'; this.ctx.lineWidth = 2; this.ctx.beginPath(); this.ctx.ellipse(at.x, at.y + 5, 25, 10, 0, 0, Math.PI * 2); this.ctx.stroke();
    }

    drawSprite(image, column, row, columns, rows, x, y, width, height) {
        if (image?.complete && image.naturalWidth) {
            const sourceWidth = image.naturalWidth / columns;
            const sourceHeight = image.naturalHeight / rows;
            this.ctx.filter = 'brightness(1.22) contrast(1.08) saturate(1.1)';
            this.ctx.drawImage(image, column * sourceWidth, row * sourceHeight, sourceWidth, sourceHeight, x - width / 2, y - height + 12, width, height);
            this.ctx.filter = 'none';
            return;
        }
        this.ctx.fillStyle = '#b94d48'; this.ctx.beginPath(); this.ctx.arc(x, y - height / 2, width * .22, 0, Math.PI * 2); this.ctx.fill();
    }

    drawWeaponEffect(player, at, elapsed) {
        const weapon = player.base.weapon;
        const direction = this.projection.project(player.x + player.facing.x * 45, player.y + player.facing.y * 45, this.camera);
        const muzzleX = at.x + (direction.x - at.x) * .75;
        const muzzleY = at.y - 32 + (direction.y - at.y) * .35;
        if (weapon === 'mace' || weapon === 'knife') {
            this.ctx.strokeStyle = weapon === 'mace' ? '#bcb7aa' : '#e8ddd1'; this.ctx.lineWidth = weapon === 'mace' ? 6 : 3; this.ctx.beginPath(); this.ctx.moveTo(at.x, at.y - 36); this.ctx.lineTo(muzzleX, muzzleY); this.ctx.stroke();
            return;
        }
        this.ctx.fillStyle = weapon === 'molotov' ? '#ef713f' : '#f5d36d'; this.ctx.shadowColor = this.ctx.fillStyle; this.ctx.shadowBlur = 12 + Math.sin(elapsed * 8) * 4; this.ctx.beginPath(); this.ctx.arc(muzzleX, muzzleY, weapon === 'molotov' ? 5 : 3, 0, Math.PI * 2); this.ctx.fill(); this.ctx.shadowBlur = 0;
    }

    drawHealthBar(at, enemy) { const { ctx } = this; const width = enemy.isBoss ? 74 : 42; ctx.fillStyle = 'rgba(15,8,8,.85)'; ctx.fillRect(at.x - width / 2, at.y - enemy.radius - 18, width, 5); ctx.fillStyle = enemy.isBoss ? '#f29a52' : '#da5b57'; ctx.fillRect(at.x - width / 2, at.y - enemy.radius - 18, width * Math.max(0, enemy.health / enemy.maxHealth), 5); if (enemy.isBoss) { ctx.fillStyle = '#f5e5cb'; ctx.font = '700 11px sans-serif'; ctx.textAlign = 'center'; ctx.fillText(enemy.name, at.x, at.y - enemy.radius - 24); } }
    drawFireZones(zones) { for (const zone of zones) { const at = this.project(zone); const glow = this.ctx.createRadialGradient(at.x, at.y, 2, at.x, at.y, zone.radius * .7); glow.addColorStop(0, 'rgba(255,237,124,.76)'); glow.addColorStop(.45, 'rgba(240,91,42,.42)'); glow.addColorStop(1, 'rgba(112,20,12,0)'); this.ctx.fillStyle = glow; this.ctx.beginPath(); this.ctx.ellipse(at.x, at.y, zone.radius, zone.radius * .42, 0, 0, Math.PI * 2); this.ctx.fill(); } }
    drawOrbs(orbs, elapsed) { for (const orb of orbs) { const at = this.project(orb); this.ctx.fillStyle = '#96f4cb'; this.ctx.shadowColor = '#69e6b3'; this.ctx.shadowBlur = 18; this.ctx.beginPath(); this.ctx.arc(at.x, at.y - 5 + Math.sin(elapsed * 5 + orb.x) * 2, orb.radius, 0, Math.PI * 2); this.ctx.fill(); this.ctx.shadowBlur = 0; } }
    drawProjectiles(projectiles) { for (const shot of projectiles) { const at = this.project(shot); const past = this.projection.project(shot.x - shot.vx * .035, shot.y - shot.vy * .035, this.camera); this.ctx.strokeStyle = shot.color; this.ctx.lineWidth = Math.max(2, shot.radius / 2); this.ctx.beginPath(); this.ctx.moveTo(past.x, past.y); this.ctx.lineTo(at.x, at.y); this.ctx.stroke(); this.ctx.fillStyle = shot.color; this.ctx.beginPath(); this.ctx.arc(at.x, at.y, shot.radius, 0, Math.PI * 2); this.ctx.fill(); } }
    drawShadow(x, y, radius) { this.ctx.fillStyle = 'rgba(0,0,0,.48)'; this.ctx.beginPath(); this.ctx.ellipse(x, y + 5, radius * .9, radius * .35, 0, 0, Math.PI * 2); this.ctx.fill(); }
    drawExperienceBar(player) { this.ctx.fillStyle = 'rgba(0,0,0,.55)'; this.ctx.fillRect(0, this.height - 6, this.width, 6); this.ctx.fillStyle = '#69d1b0'; this.ctx.fillRect(0, this.height - 6, this.width * Math.min(1, player.xp / player.xpToNext), 6); }
    drawPause() { this.ctx.fillStyle = 'rgba(7,8,8,.5)'; this.ctx.fillRect(0, 0, this.width, this.height); this.ctx.fillStyle = '#f0eee9'; this.ctx.font = '700 22px sans-serif'; this.ctx.textAlign = 'center'; this.ctx.fillText('PAUSE', this.width / 2, this.height / 2); }
}
