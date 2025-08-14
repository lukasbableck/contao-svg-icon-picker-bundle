import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
	selectedIcon = this.element.querySelector('.selected-icon');
	inputField = this.element.querySelector('input[type="hidden"]');
	picker = this.element.querySelector('.picker-container');

	connect() {
		if (this.inputField.value) {
			this.setActiveIcon(this.inputField.value);
		}
	}

	togglePicker() {
		this.picker.classList.toggle('active');
	}

	filterIcons(event) {
		const searchTerm = event.target.value.toLowerCase();
		const icons = this.element.querySelectorAll('.icon-item');
		icons.forEach((icon) => {
			const iconName = icon.getAttribute('data-icon-name').toLowerCase();
			const searchterms = JSON.parse(icon.getAttribute('data-search-terms')) || [];
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
		this.setActiveIcon(selectedIcon.dataset.value);
		this.setSelectedIcon(selectedIcon);
	}

	setActiveIcon(value) {
		this.inputField.value = value;

		this.element.querySelectorAll('.icon-item').forEach((i) => {
			if (i.dataset.value == value) {
				i.classList.add('active');
				return;
			}
			i.classList.remove('active');
		});
	}

	setSelectedIcon(icon) {
		this.selectedIcon.innerHTML = icon.querySelector('.icon').innerHTML;
	}
}
