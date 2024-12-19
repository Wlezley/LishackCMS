import 'bootstrap';
import naja from 'naja';

// jQuery alias
window.$ = window.jQuery = $;

// Naja binding
document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));

// TODO: Naja post-process (reloading, etc..)
// naja.addEventListener('complete', () => { });
