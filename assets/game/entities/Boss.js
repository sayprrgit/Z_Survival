import { Enemy } from './Enemy.js';

export class Boss extends Enemy {
    constructor(x, y, definition, scaling) {
        super(x, y, definition, scaling);
        this.enrage = 1.15;
        this.radius = Math.max(this.radius, 28);
    }
}
