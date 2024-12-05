import 'bootstrap';
import dropzone from 'dropzone';
import naja from 'naja';
import webpPolyfill from './webp';
import lazyloading from './lazyloading';

window.$ = window.jQuery = $;

document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));
webpPolyfill();
lazyloading();

naja.addEventListener('complete', () => {
  webpPolyfill();
  lazyloading();
});
