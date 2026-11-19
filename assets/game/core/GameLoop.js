export class GameLoop {
    constructor(update, render) { this.update = update; this.render = render; this.last = 0; this.running = false; }
    start() { this.running = true; requestAnimationFrame((time) => this.frame(time)); }
    frame(time) {
        if (!this.running) return;
        const dt = Math.min(0.05, (time - (this.last || time)) / 1000);
        this.last = time;
        this.update(dt);
        this.render();
        requestAnimationFrame((next) => this.frame(next));
    }
}
