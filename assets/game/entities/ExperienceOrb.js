export class ExperienceOrb {
    constructor(x, y, experience, boss = false) {
        Object.assign(this, { x, y, experience, radius: boss ? 8 : 5, collected: false });
    }
}
