import LazyLoad from 'vanilla-lazyload';

export default function lazyloading() {
  new LazyLoad({
    elements_selector: '.lazy'
  });
}
