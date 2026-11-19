const TRANSITION_DURATION = 360;

const initialiseSurvivorSelectors = () => {
    document.querySelectorAll('[data-survivor-roster]').forEach((roster) => {
        const panels = [...roster.querySelectorAll('[data-survivor-panel]')];
        const buttons = [...roster.querySelectorAll('[data-survivor-select]')];
        let currentPanel = roster.querySelector('.survivor-panel.is-active') ?? panels[0];

        if (!currentPanel) {
            return;
        }

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

            currentPanel.classList.remove('is-active');
            currentPanel.classList.add('is-leaving');
            currentPanel.setAttribute('aria-hidden', 'true');

            nextPanel.classList.add('is-preparing');
            nextPanel.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => {
                nextPanel.classList.remove('is-preparing');
                nextPanel.classList.add('is-active');
            });

            const previousPanel = currentPanel;
            currentPanel = nextPanel;
            window.setTimeout(() => previousPanel.classList.remove('is-leaving'), TRANSITION_DURATION);
        };

        panels.forEach((panel) => {
            const isActive = panel === currentPanel;
            panel.classList.toggle('is-active', isActive);
            panel.setAttribute('aria-hidden', String(!isActive));
        });
        buttons.forEach((button) => {
            const isActive = button.dataset.survivorSelect === currentPanel.dataset.survivorPanel;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
            button.addEventListener('click', () => select(button.dataset.survivorSelect));
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiseSurvivorSelectors, { once: true });
} else {
    initialiseSurvivorSelectors();
}
