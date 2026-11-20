import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import './survivor-selector.js';

let gameModule;

const loadGameWhenNeeded = () => {
    if (document.querySelector('[data-game-root]') && !gameModule) {
        gameModule = import('./game/game-entry.js');
    }
};

document.addEventListener('turbo:load', loadGameWhenNeeded);
loadGameWhenNeeded();
