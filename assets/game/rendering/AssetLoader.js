export class AssetLoader {
    constructor() { this.assets = new Map(); }

    load(key, source) {
        const image = new Image();
        image.src = source;
        this.assets.set(key, image);
        return image;
    }

    get(key) { return this.assets.get(key); }
}
