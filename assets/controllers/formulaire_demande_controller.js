import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['type', 'prestation', 'vehicule', 'message', 'counter', 'errorSummary'];

    connect() {
        this.updateConditionalFields();
        this.updateCounter();
        if (this.hasErrorSummaryTarget) {
            this.errorSummaryTarget.setAttribute('tabindex', '-1');
            this.errorSummaryTarget.focus();
        }
    }

    updateConditionalFields() {
        const checked = this.typeTargets.find((field) => field.checked);
        const value = checked?.value ?? '';
        this.setFieldState(this.prestationTarget, value === 'devis');
        this.setFieldState(this.vehiculeTarget, value === 'vehicule');
    }

    updateCounter() {
        if (!this.hasMessageTarget || !this.hasCounterTarget) return;
        const maximum = Number(this.messageTarget.maxLength) || 2000;
        this.counterTarget.textContent = `${this.messageTarget.value.length} / ${maximum}`;
    }

    setFieldState(wrapper, visible) {
        wrapper.hidden = !visible;
        wrapper.querySelectorAll('select, input, textarea').forEach((field) => {
            field.disabled = !visible;
        });
    }
}
