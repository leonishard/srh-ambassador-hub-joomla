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

		if (toggle) {
			toggle.addEventListener('click', function () {
				var next = root.getAttribute('data-ah-theme') === 'dark' ? 'light' : 'dark';
				applyTheme(next);

				try {
					window.localStorage.setItem(STORAGE_KEY, next);
				} catch (e) {
					/* localStorage unavailable (private browsing, etc.) -- theme just won't persist. */
				}
			});
		}
	});

	// Generic click-to-reveal disclosure: a button with data-ambassadorhub-toggle="panel-id"
	// shows/hides #panel-id, and swaps its own label between data-label-show/data-label-hide
	// when both are present -- used for "+ New Group"/"+ New Event"/"+ New Post" style forms
	// that should stay out of the way until the user asks for them.
	document.addEventListener('click', function (event) {
		var trigger = event.target.closest('[data-ambassadorhub-toggle]');

		if (!trigger) {
			return;
		}

		var target = document.getElementById(trigger.getAttribute('data-ambassadorhub-toggle'));

		if (!target) {
			return;
		}

		var willShow = target.hasAttribute('hidden');

		if (willShow) {
			target.removeAttribute('hidden');
		} else {
			target.setAttribute('hidden', '');
		}

		var showLabel = trigger.getAttribute('data-label-show');
		var hideLabel = trigger.getAttribute('data-label-hide');

		if (showLabel && hideLabel) {
			trigger.textContent = willShow ? hideLabel : showLabel;
		}
	});
})();
