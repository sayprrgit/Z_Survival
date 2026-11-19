import { GameLoop } from './GameLoop.js';
import { GameState } from './GameState.js';
import { InputManager } from './InputManager.js';
import { Enemy } from '../entities/Enemy.js';
import { CombatSystem } from '../systems/CombatSystem.js';
import { UpgradeSystem } from '../systems/UpgradeSystem.js';

export class Game {
    constructor(root, configuration) {
        this.root = root;
        this.canvas = root.querySelector('[data-game-canvas]');
        this.ctx = this.canvas.getContext('2d');
        this.state = new GameState(configuration);
        this.input = new InputManager();
        this.combat = new CombatSystem();
        this.upgrades = new UpgradeSystem(this.state);
        this.loop = new GameLoop((dt) => this.update(dt), () => this.render());
        this.resize = () => this.resizeCanvas();
        window.addEventListener('resize', this.resize);
        this.resizeCanvas();
        this.loop.start();
    }

    resizeCanvas() {
        const box = this.canvas.getBoundingClientRect();
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        this.canvas.width = Math.floor(box.width * ratio);
        this.canvas.height = Math.floor(box.height * ratio);
        this.ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        this.width = box.width; this.height = box.height;
    }

    update(dt) {
        const s = this.state, p = s.player;
        if (s.paused || s.over) return;
        s.elapsed += dt;
        const movement = this.input.vector();
        p.x += movement.x * p.moveSpeed * dt;
        p.y += movement.y * p.moveSpeed * dt;
        p.health = Math.min(p.maxHealth, p.health + p.regeneration * dt);
        s.spawnTimer -= dt;
        if (s.spawnTimer <= 0) {
            const angle = Math.random() * Math.PI * 2;
            const radius = Math.max(this.width, this.height) * 0.65;
            s.enemies.push(new Enemy(p.x + Math.cos(angle) * radius, p.y + Math.sin(angle) * radius, p.level));
            s.spawnTimer = Math.max(0.26, 1.2 - s.elapsed / 180);
        }
        for (const enemy of s.enemies) {
            const dx = p.x - enemy.x, dy = p.y - enemy.y, distance = Math.hypot(dx, dy) || 1;
            if (distance > p.radius + enemy.radius) { enemy.x += dx / distance * enemy.speed * dt; enemy.y += dy / distance * enemy.speed * dt; }
            enemy.hitCooldown -= dt;
            if (distance < p.radius + enemy.radius + 2 && enemy.hitCooldown <= 0) {
                p.takeDamage(enemy.damage * (1 - Math.min(0.8, p.base.damageReduction)));
                enemy.hitCooldown = 0.8;
            }
        }
        this.combat.update(s, dt);
        for (const projectile of s.projectiles) {
            projectile.x += projectile.vx * dt; projectile.y += projectile.vy * dt;
            for (const enemy of s.enemies) {
                if (enemy.dead || projectile.hit.has(enemy)) continue;
                if ((enemy.x - projectile.x) ** 2 + (enemy.y - projectile.y) ** 2 < (enemy.radius + projectile.radius) ** 2) {
                    enemy.takeDamage(projectile.damage); projectile.hit.add(enemy);
                    if (projectile.pierce-- <= 0) projectile.dead = true;
                    break;
                }
            }
        }
        s.projectiles = s.projectiles.filter((shot) => !shot.dead && Math.abs(shot.x-p.x) < 900 && Math.abs(shot.y-p.y) < 900);
        for (const enemy of s.enemies) if (enemy.dead && !enemy.rewarded) {
            enemy.rewarded = true; s.kills++;
            s.orbs.push({ x: enemy.x, y: enemy.y, xp: 1 + Math.floor(s.elapsed / 90), radius: 5 });
        }
        s.enemies = s.enemies.filter((enemy) => !enemy.dead);
        for (const orb of s.orbs) {
            const dx = p.x-orb.x, dy = p.y-orb.y, distance = Math.hypot(dx, dy) || 1;
            if (distance < p.pickupRadius) { orb.x += dx / distance * Math.min(380, (p.pickupRadius-distance+60)*5) * dt; orb.y += dy / distance * Math.min(380, (p.pickupRadius-distance+60)*5) * dt; }
            if (distance < p.radius + orb.radius + 4) { p.xp += orb.xp * (1 + p.bonuses.xp); orb.collected = true; }
        }
        s.orbs = s.orbs.filter((orb) => !orb.collected);
        while (p.xp >= p.xpToNext) {
            p.xp -= p.xpToNext; p.level++; p.xpToNext = Math.floor(5 + p.level * 2.3);
            this.upgrades.open(); break;
        }
        if (p.dead) this.end();
        this.updateHud();
    }

    render() {
        const ctx = this.ctx, s = this.state, p = s.player;
        ctx.clearRect(0, 0, this.width, this.height);
        ctx.fillStyle = '#171918'; ctx.fillRect(0, 0, this.width, this.height);
        const left = p.x - this.width/2, top = p.y - this.height/2;
        ctx.strokeStyle = 'rgba(199,203,181,.08)'; ctx.lineWidth = 1;
        const grid = 48, sx = -((left % grid) + grid) % grid, sy = -((top % grid) + grid) % grid;
        for (let x=sx; x<this.width; x+=grid) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,this.height); ctx.stroke(); }
        for (let y=sy; y<this.height; y+=grid) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(this.width,y); ctx.stroke(); }
        const xy = (entity) => ({ x: entity.x-left, y: entity.y-top });
        for (const zone of s.fireZones) { const at=xy(zone); ctx.fillStyle='rgba(240,91,42,.2)'; ctx.beginPath(); ctx.arc(at.x,at.y,zone.radius,0,Math.PI*2); ctx.fill(); }
        for (const orb of s.orbs) { const at=xy(orb); ctx.fillStyle='#7cddbd'; ctx.shadowColor='#7cddbd'; ctx.shadowBlur=12; ctx.beginPath(); ctx.arc(at.x,at.y,orb.radius,0,Math.PI*2); ctx.fill(); ctx.shadowBlur=0; }
        for (const shot of s.projectiles) { const at=xy(shot); ctx.fillStyle=shot.color; ctx.beginPath(); ctx.arc(at.x,at.y,shot.radius,0,Math.PI*2); ctx.fill(); }
        for (const enemy of s.enemies) { const at=xy(enemy); ctx.fillStyle=enemy.color; ctx.beginPath(); ctx.arc(at.x,at.y,enemy.radius,0,Math.PI*2); ctx.fill(); ctx.fillStyle='#302d29'; ctx.fillRect(at.x-enemy.radius,at.y-enemy.radius-7,enemy.radius*2,3); ctx.fillStyle='#ba594d'; ctx.fillRect(at.x-enemy.radius,at.y-enemy.radius-7,enemy.radius*2*Math.max(0,enemy.health/enemy.maxHealth),3); }
        ctx.fillStyle='#e9c681'; ctx.beginPath(); ctx.arc(this.width/2,this.height/2,p.radius,0,Math.PI*2); ctx.fill();
        ctx.fillStyle='rgba(0,0,0,.35)'; ctx.fillRect(0,this.height-5,this.width,5);
        ctx.fillStyle='#76c8a4'; ctx.fillRect(0,this.height-5,this.width*Math.min(1,p.xp/p.xpToNext),5);
    }

    updateHud() {
        const p=this.state.player, s=this.state;
        this.root.querySelector('[data-level]').textContent=p.level;
        this.root.querySelector('[data-kills]').textContent=s.kills;
        this.root.querySelector('[data-health]').textContent=Math.ceil(p.health);
        this.root.querySelector('[data-health-bar]').style.width=`${100*p.health/p.maxHealth}%`;
        const seconds=Math.floor(s.elapsed);
        this.root.querySelector('[data-time]').textContent=`${String(Math.floor(seconds/60)).padStart(2,'0')}:${String(seconds%60).padStart(2,'0')}`;
    }

    end() {
        this.state.over=true;
        const panel=this.root.querySelector('[data-game-over]'); panel.hidden=false;
        this.root.querySelector('[data-end-title]').textContent='Fin de partie';
        this.root.querySelector('[data-end-stats]').textContent=`Survie : ${Math.floor(this.state.elapsed)} s · Éliminations : ${this.state.kills} · Niveau ${this.state.player.level}`;
    }
}
