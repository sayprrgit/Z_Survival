export class InputManager {
    constructor(target = window) {
        this.keys = new Set();
        this.actions = new Set();
        this.target = target;
        this.down = (event) => { const key = event.key.toLowerCase(); if (['arrowup','arrowdown','arrowleft','arrowright',' ','escape'].includes(key)) event.preventDefault(); if (key === 'escape' || key === 'p') this.actions.add('pause'); if (key === 'f') this.actions.add('fullscreen'); this.keys.add(key); };
        this.up = (event) => this.keys.delete(event.key.toLowerCase());
        target.addEventListener('keydown', this.down);
        target.addEventListener('keyup', this.up);
    }
    vector() {
        const x = Number(this.keys.has('d') || this.keys.has('arrowright')) - Number(this.keys.has('a') || this.keys.has('q') || this.keys.has('arrowleft'));
        const y = Number(this.keys.has('s') || this.keys.has('arrowdown')) - Number(this.keys.has('w') || this.keys.has('z') || this.keys.has('arrowup'));
        const length = Math.hypot(x, y) || 1;
        return { x: x / length, y: y / length };
    }
    consume(action) { const present = this.actions.has(action); this.actions.delete(action); return present; }
    destroy() { this.target.removeEventListener('keydown', this.down); this.target.removeEventListener('keyup', this.up); }
}
