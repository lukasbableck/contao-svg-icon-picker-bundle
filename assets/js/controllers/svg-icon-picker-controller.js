import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        if (this.inputField.value) {
            this._waitForFrame().then(() => this._setActiveIcon(this.inputField.value));
        }
    }

    togglePicker() {
        this.element.querySelector('.picker-container').classList.toggle('active');
    }

    filterIcons(event) {
        const searchTerm = event.target.value.toLowerCase();
        const icons = this.element.querySelectorAll('.icon-item');
        icons.forEach((icon) => {
            const iconName = icon.getAttribute('data-icon-name').toLowerCase();
            const searchTermsAttr = icon.getAttribute('data-search-terms');
            const searchterms = searchTermsAttr ? JSON.parse(searchTermsAttr) : [];
            if (searchterms.some((term) => term.toLowerCase().includes(searchTerm)) || iconName.includes(searchTerm)) {
                icon.style.display = '';
            } else {
                icon.style.display = 'none';
            }
        });
    }

    reset() {
        this.inputField.value = '';
        this.selectedIcon.innerHTML = '';
        this.element.querySelectorAll('.icon-item').forEach((i) => {
            i.classList.remove('active');
        });
    }

    selectIcon(event) {
        const selectedIcon = event.currentTarget;
        this._setActiveIcon(selectedIcon.dataset.value);
        this._setSelectedIcon(selectedIcon);
    }

    get selectedIcon() {
        return this.element.querySelector('.selected-icon');
    }

    get inputField() {
        return this.element.querySelector('input[type="hidden"]');
    }

    _setActiveIcon(value) {
        this.inputField.value = value;

        this.element.querySelectorAll('.icon-item').forEach((i) => {
            if (i.dataset.value === value) {
                i.classList.add('active');
                return;
            }
            i.classList.remove('active');
        });
    }

    _setSelectedIcon(icon) {
        this.selectedIcon.innerHTML = icon.querySelector('.icon').innerHTML;
    }

    _waitForFrame() {
        return new Promise((resolve) => {
            const frame = this.element.querySelector('turbo-frame');
            if (!frame) {
                resolve();
                return;
            }

            if (frame.querySelector('.icon-selector')) {
                resolve();
                return;
            }

            frame.addEventListener('turbo:frame-load', () => resolve(), { once: true });
        });
    }
}
