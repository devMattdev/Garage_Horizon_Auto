import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['titre', 'label', 'montant', 'description'];

    afficher(event) {
        const { titre, description, montant } = event.params;

        this.titreTarget.textContent = titre;
        this.descriptionTarget.textContent = description;
        this.labelTarget.hidden = true;

        if (montant) {
            this.montantTarget.textContent = montant;
            this.montantTarget.hidden = false;
        } else {
            this.montantTarget.hidden = true;
        }

        this.element.querySelectorAll('.service-step').forEach((bouton) => {
            bouton.classList.toggle('is-active', bouton === event.currentTarget);
        });
    }
}
