const initialisedRosters = new WeakSet();
const initialiseSurvivorSelectors = () => {
    document.querySelectorAll('[data-survivor-roster]').forEach((roster) => {
        const panels = [...roster.querySelectorAll('[data-survivor-panel]')];
        const buttons = [...roster.querySelectorAll('[data-survivor-select]')];
        let currentPanel = roster.querySelector('.survivor-panel.is-active') ?? panels[0];
        if (!currentPanel || initialisedRosters.has(roster)) {
            return;
        }
        initialisedRosters.add(roster);

        const loadPortrait = (panel) => {
            const portrait = panel.querySelector('.survivor-art');
            if (portrait && panel.dataset.portrait && !portrait.style.backgroundImage) {
                portrait.style.backgroundImage = `url("${panel.dataset.portrait}")`;
            }
        };

        const select = (code) => {
            const nextPanel = roster.querySelector(`[data-survivor-panel="${code}"]`);
            if (!nextPanel || nextPanel === currentPanel) {
                return;
            }
            buttons.forEach((button) => {
                const isActive = button.dataset.survivorSelect === code;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', String(isActive));
            });

            currentPanel = nextPanel;
            loadPortrait(currentPanel);
            panels.forEach((panel) => {
                const isActive = panel === currentPanel;
                panel.classList.remove('is-preparing', 'is-leaving');
                panel.classList.toggle('is-active', isActive);
                panel.setAttribute('aria-hidden', String(!isActive));
            });
        };

        panels.forEach((panel) => {
            const isActive = panel === currentPanel;
            panel.classList.toggle('is-active', isActive);
            panel.setAttribute('aria-hidden', String(!isActive));
        });
        loadPortrait(currentPanel);
        buttons.forEach((button) => {
            const isActive = button.dataset.survivorSelect === currentPanel.dataset.survivorPanel;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
            button.addEventListener('click', () => select(button.dataset.survivorSelect));
        });
    });
};

document.addEventListener('turbo:load', initialiseSurvivorSelectors);
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiseSurvivorSelectors, { once: true });
} else {
    initialiseSurvivorSelectors();
}
