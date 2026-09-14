import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { id: String };

    connect() {
        try {
            if (sessionStorage.getItem(this.storageKey()) === 'closed') this.element.remove();
        } catch (_) {
            // The banner remains visible when storage is unavailable.
        }
    }

    close() {
        try {
            sessionStorage.setItem(this.storageKey(), 'closed');
        } catch (_) {
            // Closing still works when storage is unavailable.
        }
        this.element.remove();
    }

    storageKey() {
        return `horizon-auto-banner-${this.idValue}`;
    }
}
