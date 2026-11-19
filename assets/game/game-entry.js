import { Game } from './core/Game.js';

document.querySelectorAll('[data-game-root]').forEach((root) => {
    const data = root.querySelector('[data-game-config]');
    if (data) new Game(root, JSON.parse(data.textContent));
});
