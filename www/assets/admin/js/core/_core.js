import 'bootstrap';
import dropzone from 'dropzone';
import naja from 'naja';

window.$ = window.jQuery = $;

document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));

naja.addEventListener('complete', () => {
});
