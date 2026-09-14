import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['slide', 'thumb', 'status'];

    connect() {
        this.index = 0;
        this.startX = null;
        this.show(0, false);
    }

    previous() { this.show(this.index - 1); }
    next() { this.show(this.index + 1); }
    select(event) { this.show(Number(event.currentTarget.dataset.index)); }
    touchStart(event) { this.startX = event.changedTouches[0]?.clientX ?? null; }
    touchEnd(event) {
        if (this.startX === null) return;
        const delta = (event.changedTouches[0]?.clientX ?? this.startX) - this.startX;
        if (Math.abs(delta) > 45) delta > 0 ? this.previous() : this.next();
        this.startX = null;
    }

    keydown(event) {
        const actions = { ArrowLeft: () => this.previous(), ArrowRight: () => this.next(), Home: () => this.show(0), End: () => this.show(this.slideTargets.length - 1) };
        if (actions[event.key]) {
            event.preventDefault();
            actions[event.key]();
        }
    }

    show(requested, announce = true) {
        const count = this.slideTargets.length;
        if (!count) return;
        this.index = (requested + count) % count;
        this.slideTargets.forEach((slide, index) => {
            const active = index === this.index;
            slide.classList.toggle('is-active', active);
            slide.hidden = !active;
        });
        this.thumbTargets.forEach((thumb, index) => thumb.setAttribute('aria-current', index === this.index ? 'true' : 'false'));
        if (this.hasStatusTarget) this.statusTarget.textContent = announce ? `Photo ${this.index + 1} sur ${count}` : '';
    }
}
