export class UpgradeSystem {
    constructor(state, root) {
        this.state = state;
        this.root = root.querySelector('[data-upgrade]');
        this.options = this.root.querySelector('[data-upgrade-options]');
        this.active = false;
    }

    collectChoices() {
        const { player, definitions } = this.state;
        const choices = [];
        for (const definition of definitions.weaponsByCode.values()) {
            const owned = player.weapons.get(definition.code);
            if (owned ? owned.level < definition.maxLevel : player.weapons.size < 6) choices.push({ type: 'weapon', definition, level: (owned?.level ?? 0) + 1 });
        }
        for (const definition of definitions.powerUpsByCode.values()) {
            const owned = player.powerUps.get(definition.code);
            if (owned ? owned.level < definition.maxLevel : player.powerUps.size < 4) choices.push({ type: 'powerUp', definition, level: (owned?.level ?? 0) + 1 });
        }
        return choices.sort(() => Math.random() - 0.5).slice(0, 3);
    }

    open() {
        const choices = this.collectChoices();
        if (!choices.length) { this.state.resume('upgrade'); return; }
        this.active = true;
        this.state.pause('upgrade');
        this.root.hidden = false;
        this.options.replaceChildren();
        for (const choice of choices) {
            const button = document.createElement('button');
            button.className = 'upgrade-card';
            button.innerHTML = `<small>${choice.type === 'weapon' ? 'ARME' : 'POWER-UP'} · NIVEAU ${choice.level}</small><strong></strong><span></span>`;
            button.querySelector('strong').textContent = choice.definition.name;
            button.querySelector('span').textContent = choice.definition.description;
            button.addEventListener('click', () => this.choose(choice), { once: true });
            this.options.append(button);
        }
    }

    choose(choice) {
        if (choice.type === 'weapon') this.state.player.addWeapon(choice.definition.code);
        else {
            this.state.player.addPowerUp(choice.definition);
            if (choice.definition.effect === 'health_regeneration' && choice.definition.bonus > 0) this.state.player.health = Math.min(this.state.player.maxHealth, this.state.player.health + choice.definition.bonus * 5);
        }
        this.root.hidden = true;
        this.active = false;
        this.state.resume('upgrade');
    }
}
