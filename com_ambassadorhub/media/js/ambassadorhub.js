(function () {
	'use strict';

	var STORAGE_KEY = 'ambassadorhub-theme';
	var root = document.documentElement;

	function applyTheme(theme) {
		root.setAttribute('data-ah-theme', theme);
	}

	function currentTheme() {
		try {
			return window.localStorage.getItem(STORAGE_KEY) || 'dark';
		} catch (e) {
			return 'dark';
		}
	}

	applyTheme(currentTheme());

	document.addEventListener('DOMContentLoaded', function () {
		var toggle = document.querySelector('[data-ambassadorhub-theme-toggle]');

		if (!toggle) {
			return;
		}

		toggle.addEventListener('click', function () {
			var next = root.getAttribute('data-ah-theme') === 'dark' ? 'light' : 'dark';
			applyTheme(next);

			try {
				window.localStorage.setItem(STORAGE_KEY, next);
			} catch (e) {
				/* localStorage unavailable (private browsing, etc.) -- theme just won't persist. */
			}
		});
	});
})();
