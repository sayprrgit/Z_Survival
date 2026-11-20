export class GameLoop {
    constructor(update, render) { this.update = update; this.render = render; this.last = 0; this.running = false; this.frameId = null; }
    start() { if (this.running) return; this.running = true; this.last = 0; this.frameId = requestAnimationFrame((time) => this.frame(time)); }
    stop() { this.running = false; if (this.frameId !== null) cancelAnimationFrame(this.frameId); this.frameId = null; }
    frame(time) {
        if (!this.running) return;
        const dt = Math.min(0.05, (time - (this.last || time)) / 1000);
        this.last = time;
        this.update(dt);
        this.render();
        this.frameId = requestAnimationFrame((next) => this.frame(next));
    }
}
