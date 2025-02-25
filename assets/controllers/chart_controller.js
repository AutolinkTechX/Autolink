import { Controller } from '@hotwired/stimulus';
import { Chart } from 'chart.js/auto';

export default class extends Controller {
    connect() {
        const ctx = this.element.getContext('2d');
        const data = JSON.parse(this.element.dataset.chartValue);

        new Chart(ctx, data);
    }
}
