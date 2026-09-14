import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { url: String, title: String };

    connect() {
        if (!('IntersectionObserver' in window)) return;
        this.observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) this.load();
        }, { rootMargin: '250px' });
        this.observer.observe(this.element);
    }

    disconnect() { this.observer?.disconnect(); }

    load() {
        if (this.element.querySelector('iframe') || !this.urlValue) return;
        const frame = document.createElement('iframe');
        frame.src = this.urlValue;
        frame.title = this.titleValue || 'Plan d’accès au garage';
        frame.loading = 'lazy';
        frame.referrerPolicy = 'no-referrer';
        this.element.replaceChildren(frame);
        this.observer?.disconnect();
    }
}
