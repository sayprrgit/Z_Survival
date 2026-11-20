export class LootChest {
    constructor(x, y, type = 'upgrade') { Object.assign(this, { x, y, radius: 16, type, opened: false }); }
}
