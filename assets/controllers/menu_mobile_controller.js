import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'panel'];

    connect() {
        this.buttonTarget.hidden = false;
        this.panelTarget.hidden = true;
        this.boundOutsideClick = this.outsideClick.bind(this);
        document.addEventListener('click', this.boundOutsideClick);
    }

    disconnect() {
        document.removeEventListener('click', this.boundOutsideClick);
    }

    toggle() {
        this.panelTarget.hidden ? this.open() : this.close();
    }

    open() {
        this.panelTarget.hidden = false;
        this.buttonTarget.setAttribute('aria-expanded', 'true');
        this.focusable()[0]?.focus();
    }

    close({ restoreFocus = false } = {}) {
        this.panelTarget.hidden = true;
        this.buttonTarget.setAttribute('aria-expanded', 'false');
        if (restoreFocus) this.buttonTarget.focus();
    }

    keydown(event) {
        if (event.key === 'Escape') {
            this.close({ restoreFocus: true });
            return;
        }
        if (event.key !== 'Tab' || this.panelTarget.hidden) return;

        const items = this.focusable();
        if (!items.length) return;
        const first = items[0];
        const last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    outsideClick(event) {
        if (!this.panelTarget.hidden && !this.element.contains(event.target)) this.close();
    }

    focusable() {
        return [...this.panelTarget.querySelectorAll('a[href], button:not([disabled])')];
    }
}
