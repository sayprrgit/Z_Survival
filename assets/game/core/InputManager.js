export class InputManager {
    constructor(target = window) {
        this.keys = new Set();
        this.down = (event) => { const key = event.key.toLowerCase(); if (['arrowup','arrowdown','arrowleft','arrowright',' '].includes(key)) event.preventDefault(); this.keys.add(key); };
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
}
