export class Camera {
    constructor() { this.x = 0; this.y = 0; this.width = 0; this.height = 0; }
    resize(width, height) { this.width = width; this.height = height; }
    follow(target) { this.x = target.x; this.y = target.y; }
    project(entity) { return { x: entity.x - this.x + this.width / 2, y: entity.y - this.y + this.height / 2 }; }
}
