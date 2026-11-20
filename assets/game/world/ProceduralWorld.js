export const WORLD_CHUNK_SIZE = 640;

function random(seed) {
    const value = Math.sin(seed * 12.9898 + 78.233) * 43758.5453;
    return value - Math.floor(value);
}

function chunkSeed(x, y, salt = 0) { return x * 374761393 + y * 668265263 + salt * 1442695041; }

export class ProceduralWorld {
    constructor() {
        this.chunks = new Map();
        this.obstacles = [];
        this.currentChunk = null;
    }

    update(position) {
        const centerX = Math.floor(position.x / WORLD_CHUNK_SIZE);
        const centerY = Math.floor(position.y / WORLD_CHUNK_SIZE);
        const key = `${centerX}:${centerY}`;
        if (key === this.currentChunk) return;
        this.currentChunk = key;
        const active = new Set();
        for (let x = centerX - 2; x <= centerX + 2; x++) for (let y = centerY - 2; y <= centerY + 2; y++) {
            const chunkKey = `${x}:${y}`;
            active.add(chunkKey);
            if (!this.chunks.has(chunkKey)) this.chunks.set(chunkKey, this.createChunk(x, y));
        }
        for (const chunkKey of this.chunks.keys()) if (!active.has(chunkKey)) this.chunks.delete(chunkKey);
        this.obstacles = [...this.chunks.values()].flatMap((chunk) => chunk.props);
    }

    createChunk(chunkX, chunkY) {
        const props = [];
        const originX = chunkX * WORLD_CHUNK_SIZE;
        const originY = chunkY * WORLD_CHUNK_SIZE;
        const amount = 2 + Math.floor(random(chunkSeed(chunkX, chunkY, 1)) * 2);
        for (let index = 0; index < amount; index++) {
            const seed = chunkSeed(chunkX, chunkY, index + 2);
            const type = random(seed) < .22 ? 'building' : random(seed + 1) < .56 ? 'wreck' : 'barricade';
            const radius = type === 'building' ? 86 : type === 'wreck' ? 34 : 42;
            const x = originX + 100 + random(seed + 2) * (WORLD_CHUNK_SIZE - 200);
            const y = originY + 100 + random(seed + 3) * (WORLD_CHUNK_SIZE - 200);
            if (Math.hypot(x, y) < 160) continue;
            props.push({ id: `${chunkX}:${chunkY}:${index}`, type, x, y, radius, rotation: random(seed + 4) * Math.PI * 2, variant: Math.floor(random(seed + 5) * 3) });
        }
        return { key: `${chunkX}:${chunkY}`, x: chunkX, y: chunkY, props };
    }

    resolveCircle(entity, radius = entity.radius) {
        for (const obstacle of this.obstacles) {
            const dx = entity.x - obstacle.x;
            const dy = entity.y - obstacle.y;
            const minimum = radius + obstacle.radius;
            const distanceSquared = dx * dx + dy * dy;
            if (distanceSquared >= minimum * minimum) continue;
            if (distanceSquared === 0) {
                entity.x = obstacle.x + minimum;
                continue;
            }
            const distance = Math.sqrt(distanceSquared) || 1;
            entity.x = obstacle.x + dx / distance * minimum;
            entity.y = obstacle.y + dy / distance * minimum;
        }
    }

    isBlocked(x, y, radius) {
        return this.obstacles.some((obstacle) => {
            const dx = x - obstacle.x;
            const dy = y - obstacle.y;
            const minimum = radius + obstacle.radius;
            return dx * dx + dy * dy < minimum * minimum;
        });
    }
}
