import { Controller } from '@hotwired/stimulus';

/*
 * Handles the quantity selector buttons on the product page.
 * data-controller="quantity" on the wrapper, data-quantity-target="input" on the input,
 * data-action="quantity#decrease"/"quantity#increase" on the buttons.
 */
export default class extends Controller {
    static targets = ['input'];

    decrease() {
        const min = parseInt(this.inputTarget.min, 10) || 1;
        const value = this.currentValue - 1;

        this.inputTarget.value = Math.max(min, value);
    }

    increase() {
        const max = parseInt(this.inputTarget.max, 10) || Infinity;
        const value = this.currentValue + 1;

        this.inputTarget.value = Math.min(max, value);
    }

    get currentValue() {
        return parseInt(this.inputTarget.value, 10) || 1;
    }
}
