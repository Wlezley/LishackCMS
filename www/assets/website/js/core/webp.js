import { WebpMachine, detectWebpSupport, defaultDetectWebpImage } from 'webp-hero';
import { Webp } from "webp-hero/libwebp/dist/webp.js"

export default class FixedWebpMachine extends WebpMachine {
    constructor() {
        const opts = {
            webp: new Webp(),
            webpSupport: detectWebpSupport(),
            detectWebpImage: defaultDetectWebpImage
        };
        opts.webp.Module.doNotCaptureKeyboard = true;
        super(opts);
    }
}
