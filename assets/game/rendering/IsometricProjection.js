export class IsometricProjection {
    constructor(tileWidth = 76, tileHeight = 38) {
        this.tileWidth = tileWidth;
        this.tileHeight = tileHeight;
    }

    project(x, y, camera) {
        const relativeX = x - camera.x;
        const relativeY = y - camera.y;
        return {
            x: camera.width / 2 + (relativeX - relativeY) * .5,
            y: camera.height / 2 + (relativeX + relativeY) * .25,
        };
    }
}
