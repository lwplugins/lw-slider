/**
 * LW Slider Frontend JS
 *
 * Initializes Splide.js sliders from data attributes.
 */
(function () {
	'use strict';

	/**
	 * Check for reduced motion preference.
	 *
	 * @return {boolean}
	 */
	function prefersReducedMotion() {
		return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	/**
	 * Initialize all sliders on the page.
	 */
	function initSliders() {
		var sliders = document.querySelectorAll('[data-lw-slider]');

		sliders.forEach(function (el) {
			var config;

			try {
				config = JSON.parse(el.getAttribute('data-lw-slider'));
			} catch (e) {
				return;
			}

			if (prefersReducedMotion()) {
				config.autoplay = false;
				config.speed = 0;
				config.rewindSpeed = 0;
			}

			new Splide(el, config).mount();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initSliders);
	} else {
		initSliders();
	}
})();
