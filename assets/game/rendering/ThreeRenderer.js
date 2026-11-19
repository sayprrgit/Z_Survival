import * as THREE from 'three';

const palette = { mason: 0x6d7e54, raven: 0xa54342, boris: 0x53708d, eva: 0xd3d0c7, walker: 0x71865d, runner: 0xbf7445, brute: 0x80515c, spitter: 0x7eaa6e, colossus: 0x9f392f, patient_zero: 0xb95a43 };

export class ThreeRenderer {
    constructor(canvas) {
        this.canvas = canvas;
        this.scale = .025;
        this.width = 0;
        this.height = 0;
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x080a0b);
        this.scene.fog = new THREE.Fog(0x252b30, 22, 62);
        this.camera = new THREE.PerspectiveCamera(48, 1, .1, 200);
        this.cameraOffset = new THREE.Vector3(8, 10, 8);
        this.camera.position.copy(this.cameraOffset);
        this.renderer = new THREE.WebGLRenderer({ canvas, antialias: true, powerPreference: 'high-performance' });
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        this.renderer.outputColorSpace = THREE.SRGBColorSpace;
        this.units = new Map();
        this.projectiles = new Map();
        this.orbs = new Map();
        this.zones = new Map();
        this.cameraTarget = new THREE.Vector3();
        this.setupWorld();
    }

    setupWorld() {
        const ambient = new THREE.HemisphereLight(0x87a9bf, 0x1b1310, 1.7);
        this.scene.add(ambient);
        const moon = new THREE.DirectionalLight(0xd7e8f2, 2.1);
        moon.position.set(-12, 20, 8); moon.castShadow = true; moon.shadow.mapSize.set(1024, 1024); this.scene.add(moon);
        const warning = new THREE.PointLight(0xe54e3e, 13, 18, 2);
        warning.position.set(0, 5, -6); this.scene.add(warning);
        const ground = new THREE.Mesh(new THREE.PlaneGeometry(180, 180, 32, 32), new THREE.MeshStandardMaterial({ color: 0x4b5254, roughness: .95, metalness: .05 }));
        ground.rotation.x = -Math.PI / 2; ground.receiveShadow = true; this.scene.add(ground);
        const grid = new THREE.GridHelper(180, 70, 0x80413b, 0x34403d);
        grid.material.opacity = .28; grid.material.transparent = true; this.scene.add(grid);
        this.addEnvironment();
    }

    addEnvironment() {
        const concrete = new THREE.MeshStandardMaterial({ color: 0x2b302f, roughness: .9 });
        const rust = new THREE.MeshStandardMaterial({ color: 0x6e3326, roughness: .72, metalness: .28 });
        for (let index = 0; index < 34; index++) {
            const angle = index * 2.4;
            const distance = 16 + (index % 7) * 4;
            const width = 1.4 + (index % 3) * .7;
            const height = 2 + (index % 5) * 1.2;
            const building = new THREE.Mesh(new THREE.BoxGeometry(width, height, width), concrete);
            building.position.set(Math.cos(angle) * distance, height / 2, Math.sin(angle) * distance); building.rotation.y = angle; building.castShadow = true; building.receiveShadow = true; this.scene.add(building);
        }
        for (let index = 0; index < 18; index++) {
            const angle = index * 1.7; const distance = 5 + (index % 4) * 3;
            const barrel = new THREE.Mesh(new THREE.CylinderGeometry(.28, .32, .72, 12), rust);
            barrel.position.set(Math.cos(angle) * distance, .36, Math.sin(angle) * distance); barrel.castShadow = true; this.scene.add(barrel);
        }
    }

    resize() {
        const box = this.canvas.getBoundingClientRect();
        this.width = box.width; this.height = box.height;
        this.camera.aspect = this.width / Math.max(1, this.height);
        this.camera.updateProjectionMatrix();
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
        this.renderer.setSize(this.width, this.height, false);
    }

    render(state) {
        this.syncUnit('player', state.player, true, state.elapsed);
        const active = new Set(['player']);
        for (const enemy of state.enemies) { const key = `enemy-${enemy.runtimeId ?? enemy.code}`; active.add(key); this.syncUnit(key, enemy, false, state.elapsed); }
        this.removeMissing(this.units, active);
        this.syncEffects(state, state.elapsed);
        const target = this.toWorld(state.player.x, state.player.y);
        this.cameraTarget.lerp(target, .11);
        const desired = target.clone().add(this.cameraOffset);
        this.camera.position.lerp(desired, .09);
        this.camera.lookAt(this.cameraTarget.x, 0, this.cameraTarget.z);
        this.renderer.render(this.scene, this.camera);
    }

    syncUnit(key, entity, isPlayer, elapsed) {
        let model = this.units.get(key);
        if (!model) { model = this.createUnit(entity, isPlayer); this.units.set(key, model); this.scene.add(model); }
        const point = this.toWorld(entity.x, entity.y);
        model.position.set(point.x, 0, point.z);
        const moving = isPlayer ? entity.isMoving : entity.hitCooldown > entity.attackInterval - .14;
        model.position.y = Math.sin(elapsed * (moving ? 8 : 3) + entity.x * .03) * (isPlayer ? .045 : .028);
        if (isPlayer && entity.facing) model.rotation.y = Math.atan2(entity.facing.x, entity.facing.y);
        if (!isPlayer) model.rotation.y = Math.atan2(-entity.x + (this.cameraTarget.x / this.scale), -entity.y + (this.cameraTarget.z / this.scale));
        model.userData.health.scale.x = Math.max(.01, entity.health / entity.maxHealth);
        model.userData.health.position.x = -(1 - model.userData.health.scale.x) * .5;
        if (model.userData.glow) model.userData.glow.intensity = entity.attackFlash > 0 ? 4 : 1.4;
        if (model.userData.arms) model.userData.arms.rotation.x = Math.sin(elapsed * (moving ? 9 : 2)) * .36;
    }

    createUnit(entity, isPlayer) {
        const group = new THREE.Group();
        const code = isPlayer ? entity.base.code : entity.code;
        const boss = !isPlayer && entity.isBoss;
        const size = boss ? 1.45 : code === 'brute' ? 1.2 : 1;
        const material = new THREE.MeshStandardMaterial({ color: palette[code] ?? 0x909090, roughness: .68, metalness: .1, emissive: boss ? 0x36100c : 0x000000 });
        const skin = new THREE.MeshStandardMaterial({ color: boss ? 0xd4896d : 0xc8b29d, roughness: .8 });
        const body = new THREE.Mesh(new THREE.CapsuleGeometry(.28 * size, .62 * size, 5, 10), material);
        body.position.y = .62 * size; body.castShadow = true; group.add(body);
        const head = new THREE.Mesh(new THREE.SphereGeometry(.22 * size, 16, 12), skin);
        head.position.y = 1.1 * size; head.castShadow = true; group.add(head);
        const arms = new THREE.Group();
        for (const side of [-1, 1]) { const arm = new THREE.Mesh(new THREE.CapsuleGeometry(.08 * size, .35 * size, 4, 8), material); arm.position.set(side * .32 * size, .7 * size, 0); arm.rotation.z = side * .48; arm.castShadow = true; arms.add(arm); }
        group.add(arms);
        const legs = new THREE.Group();
        for (const side of [-1, 1]) { const leg = new THREE.Mesh(new THREE.CapsuleGeometry(.1 * size, .35 * size, 4, 8), material); leg.position.set(side * .13 * size, .16 * size, 0); leg.castShadow = true; legs.add(leg); }
        group.add(legs);
        if (isPlayer) group.add(this.createWeapon(entity.base.weapon));
        if (boss) { const crown = new THREE.Mesh(new THREE.TorusGeometry(.62 * size, .045, 6, 18), new THREE.MeshBasicMaterial({ color: 0xff794f })); crown.rotation.x = Math.PI / 2; crown.position.y = .08; group.add(crown); const light = new THREE.PointLight(0xff613f, 1.4, 5); light.position.y = 1; group.add(light); group.userData.glow = light; }
        const healthBack = new THREE.Mesh(new THREE.PlaneGeometry(boss ? 1.4 : .75, .08), new THREE.MeshBasicMaterial({ color: 0x25100e, side: THREE.DoubleSide }));
        healthBack.position.set(0, 1.5 * size, 0); healthBack.rotation.x = -Math.PI / 2; group.add(healthBack);
        const health = new THREE.Mesh(new THREE.PlaneGeometry(1, 1), new THREE.MeshBasicMaterial({ color: boss ? 0xff9b4b : 0xe95550, side: THREE.DoubleSide }));
        health.scale.set(boss ? 1.4 : .75, .08, 1); health.position.set(0, 1.505 * size, 0); health.rotation.x = -Math.PI / 2; group.add(health);
        group.userData = { health, arms, glow: group.userData.glow };
        return group;
    }

    createWeapon(code) {
        const group = new THREE.Group();
        const metal = new THREE.MeshStandardMaterial({ color: code === 'molotov' ? 0x9a3d25 : 0x303638, metalness: .7, roughness: .36 });
        const length = code === 'mace' ? .62 : code === 'knife' ? .36 : .72;
        const weapon = new THREE.Mesh(new THREE.BoxGeometry(.1, .1, length), metal);
        weapon.position.set(.28, .75, -.18); weapon.rotation.x = Math.PI / 2; weapon.rotation.z = -.4; weapon.castShadow = true; group.add(weapon);
        if (code === 'mace') { const head = new THREE.Mesh(new THREE.SphereGeometry(.15, 10, 8), metal); head.position.set(.28, .75, -.5); group.add(head); }
        if (code === 'molotov') { const fire = new THREE.PointLight(0xff682f, 1.2, 3); fire.position.set(.28, .82, -.2); group.add(fire); }
        return group;
    }

    syncEffects(state, elapsed) {
        this.syncCollection(this.projectiles, state.projectiles, (shot) => { const mesh = new THREE.Mesh(new THREE.SphereGeometry(.09, 10, 8), new THREE.MeshBasicMaterial({ color: shot.color })); this.scene.add(mesh); return mesh; }, (mesh, shot) => { const point = this.toWorld(shot.x, shot.y); mesh.position.set(point.x, .52, point.z); });
        this.syncCollection(this.orbs, state.orbs, () => { const mesh = new THREE.Mesh(new THREE.SphereGeometry(.11, 12, 10), new THREE.MeshStandardMaterial({ color: 0x6ce4b5, emissive: 0x2b9c7d, emissiveIntensity: 2 })); this.scene.add(mesh); return mesh; }, (mesh, orb) => { const point = this.toWorld(orb.x, orb.y); mesh.position.set(point.x, .35 + Math.sin(elapsed * 5 + orb.x) * .06, point.z); });
        this.syncCollection(this.zones, state.fireZones, () => { const mesh = new THREE.Mesh(new THREE.CircleGeometry(1, 28), new THREE.MeshBasicMaterial({ color: 0xef542d, transparent: true, opacity: .38, side: THREE.DoubleSide })); mesh.rotation.x = -Math.PI / 2; this.scene.add(mesh); return mesh; }, (mesh, zone) => { const point = this.toWorld(zone.x, zone.y); mesh.position.set(point.x, .015, point.z); mesh.scale.setScalar(zone.radius * this.scale); });
    }

    syncCollection(map, values, create, update) { const active = new Set(values); for (const value of values) { const mesh = map.get(value) ?? create(value); map.set(value, mesh); update(mesh, value); } this.removeMissing(map, active); }
    removeMissing(map, active) { for (const [key, object] of map) if (!active.has(key)) { this.scene.remove(object); map.delete(key); } }
    toWorld(x, y) { return new THREE.Vector3(x * this.scale, 0, y * this.scale); }
    destroy() { this.renderer.dispose(); }
}
