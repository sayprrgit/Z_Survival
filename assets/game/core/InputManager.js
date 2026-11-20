export class InputManager {
    constructor(target = window, pointerTarget = target) {
        this.keys = new Set();
        this.actions = new Set();
        this.weaponSlot = null;
        this.consumableSlot = null;
        this.weaponSwitchDirection = 0;
        this.pointerPosition = null;
        this.firing = false;
        this.target = target;
        this.pointerTarget = pointerTarget;
        this.releaseTarget = typeof window === 'undefined' ? target : window;
        this.down = (event) => {
            const key = event.key.toLowerCase();
            const slot = event.code.match(/^(?:Digit|Numpad)([1-6])$/)?.[1];
            if (['arrowup', 'arrowdown', 'arrowleft', 'arrowright', ' ', 'escape'].includes(key) || slot) event.preventDefault();
            if (key === 'escape' || key === 'p') this.actions.add('pause');
            if (key === 'f') this.actions.add('fullscreen');
            if (key === 'x') this.actions.add('switchWeapon');
            if (key === 'r') this.actions.add('reload');
            if (key === 'e') this.actions.add('interact');
            if (slot) this.weaponSlot = Number(slot) - 1;
            const consumableSlot = event.code.match(/^Digit([7-9])$/)?.[1];
            if (consumableSlot) this.consumableSlot = Number(consumableSlot) - 7;
            this.keys.add(key);
        };
        this.up = (event) => this.keys.delete(event.key.toLowerCase());
        this.wheel = (event) => {
            if (event.target && this.pointerTarget.contains && !this.pointerTarget.contains(event.target)) return;
            if (!event.deltaY) return;
            event.preventDefault();
            this.weaponSwitchDirection = Math.sign(event.deltaY);
        };
        this.pointerMove = (event) => { this.pointerPosition = { x: event.clientX, y: event.clientY }; };
        this.pointerDown = (event) => { if (event.button === 0) { event.preventDefault(); this.firing = true; this.pointerMove(event); } };
        this.pointerUp = (event) => { if (event.button === 0) this.firing = false; };
        target.addEventListener('keydown', this.down);
        target.addEventListener('keyup', this.up);
        target.addEventListener('wheel', this.wheel, { passive: false });
        pointerTarget.addEventListener('pointermove', this.pointerMove);
        pointerTarget.addEventListener('pointerdown', this.pointerDown);
        this.releaseTarget.addEventListener('pointerup', this.pointerUp);
    }
    vector() {
        const x = Number(this.keys.has('d') || this.keys.has('arrowright')) - Number(this.keys.has('a') || this.keys.has('q') || this.keys.has('arrowleft'));
        const y = Number(this.keys.has('s') || this.keys.has('arrowdown')) - Number(this.keys.has('w') || this.keys.has('z') || this.keys.has('arrowup'));
        const length = Math.hypot(x, y) || 1;
        return { x: x / length, y: y / length };
    }
    consume(action) { const present = this.actions.has(action); this.actions.delete(action); return present; }
    consumeWeaponSlot() { const slot = this.weaponSlot; this.weaponSlot = null; return slot; }
    consumeConsumableSlot() { const slot = this.consumableSlot; this.consumableSlot = null; return slot; }
    consumeWeaponSwitchDirection() { const direction = this.weaponSwitchDirection; this.weaponSwitchDirection = 0; return direction; }
    pointer() { return this.pointerPosition; }
    isFiring() { return this.firing; }
    destroy() { this.target.removeEventListener('keydown', this.down); this.target.removeEventListener('keyup', this.up); this.target.removeEventListener('wheel', this.wheel); this.pointerTarget.removeEventListener('pointermove', this.pointerMove); this.pointerTarget.removeEventListener('pointerdown', this.pointerDown); this.releaseTarget.removeEventListener('pointerup', this.pointerUp); }
}
