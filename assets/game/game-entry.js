import { Game } from './core/Game.js';

let activeGame;

function stopGame() {
    activeGame?.destroy();
    activeGame = undefined;
}

function mountGame() {
    const root = document.querySelector('[data-game-root]');
    if (activeGame && activeGame.root !== root) stopGame();
    if (activeGame || !root || document.documentElement.hasAttribute('data-turbo-preview')) return;
    activeGame = new Game(root, JSON.parse(root.querySelector('[data-game-config]').textContent));
}

document.addEventListener('turbo:before-cache', stopGame);
document.addEventListener('turbo:before-render', stopGame);
document.addEventListener('turbo:load', mountGame);
mountGame();
