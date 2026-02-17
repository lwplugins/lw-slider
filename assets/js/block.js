/**
 * LW Slider Gutenberg Block
 *
 * Simple card UI — select a slider by ID, no live preview.
 */
(function (wp) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;
	var Placeholder = wp.components.Placeholder;
	var Spinner = wp.components.Spinner;
	var Button = wp.components.Button;

	/**
	 * Fetch sliders from REST API.
	 */
	function useSliders() {
		var state = useState(null);
		var sliders = state[0];
		var setSliders = state[1];

		useEffect(function () {
			wp.apiFetch({ path: '/lw-slider/v1/sliders' }).then(function (data) {
				setSliders(data);
			}).catch(function () {
				setSliders([]);
			});
		}, []);

		return sliders;
	}

	/**
	 * Build slider select options.
	 */
	function buildSliderOptions(sliders) {
		var options = [{ label: '— Select a slider —', value: 0 }];

		if (!sliders) {
			return options;
		}

		sliders.forEach(function (s) {
			options.push({
				label: s.title + ' (' + s.slides + ' slides) — #' + s.id,
				value: s.id
			});
		});

		return options;
	}

	/**
	 * Find slider data by ID.
	 */
	function findSlider(sliders, id) {
		if (!sliders || !id) {
			return null;
		}
		for (var i = 0; i < sliders.length; i++) {
			if (sliders[i].id === id) {
				return sliders[i];
			}
		}
		return null;
	}

	var triStateOptions = [
		{ label: 'Slider default', value: '' },
		{ label: 'On', value: 'on' },
		{ label: 'Off', value: 'off' }
	];

	var transitionOptions = [
		{ label: 'Slider default', value: '' },
		{ label: 'Slide', value: 'slide' },
		{ label: 'Fade', value: 'fade' }
	];

	/**
	 * SVG icon for the selected card.
	 */
	function sliderIcon() {
		return el('svg', {
			width: 28, height: 28, viewBox: '0 0 24 24', fill: 'none',
			xmlns: 'http://www.w3.org/2000/svg'
		},
			el('rect', { x: 2, y: 6, width: 20, height: 12, rx: 2, stroke: '#e91e63', strokeWidth: 1.5, fill: 'none' }),
			el('circle', { cx: 9, cy: 17, r: 1, fill: '#e91e63' }),
			el('circle', { cx: 12, cy: 17, r: 1, fill: '#ccc' }),
			el('circle', { cx: 15, cy: 17, r: 1, fill: '#ccc' }),
			el('path', { d: 'M6 12l2-2v4l-2-2z', fill: '#e91e63' }),
			el('path', { d: 'M18 12l-2-2v4l2-2z', fill: '#e91e63' })
		);
	}

	registerBlockType('lw-slider/slider', {
		edit: function (props) {
			var attrs = props.attributes;
			var sliderId = attrs.sliderId;
			var setAttributes = props.setAttributes;
			var sliders = useSliders();
			var selected = findSlider(sliders, sliderId);

			var onSelectSlider = function (value) {
				setAttributes({ sliderId: parseInt(value, 10) || 0 });
			};

			var sliderSelector = el(SelectControl, {
				label: 'Slider',
				value: sliderId || 0,
				options: buildSliderOptions(sliders),
				onChange: onSelectSlider
			});

			// Inspector controls.
			var inspectorControls = el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: 'Slider Selection', initialOpen: true },
					sliders === null ? el(Spinner) : sliderSelector,
					sliderId ? el(
						Button,
						{
							variant: 'link',
							href: lwSliderBlock.adminUrl + 'post.php?post=' + sliderId + '&action=edit',
							target: '_blank',
							style: { marginTop: '4px' }
						},
						'Edit this slider \u2197'
					) : null
				),
				sliderId ? el(
					PanelBody,
					{ title: 'Override Settings', initialOpen: false },
					el('p', { className: 'components-base-control__help', style: { marginTop: 0 } },
						'Override the slider\'s saved settings for this block only.'
					),
					el(SelectControl, {
						label: 'Autoplay',
						value: attrs.overrideAutoplay,
						options: triStateOptions,
						onChange: function (v) { setAttributes({ overrideAutoplay: v }); }
					}),
					el(SelectControl, {
						label: 'Transition',
						value: attrs.overrideTransition,
						options: transitionOptions,
						onChange: function (v) { setAttributes({ overrideTransition: v }); }
					}),
					el(SelectControl, {
						label: 'Dots',
						value: attrs.overrideDots,
						options: triStateOptions,
						onChange: function (v) { setAttributes({ overrideDots: v }); }
					}),
					el(SelectControl, {
						label: 'Arrows',
						value: attrs.overrideArrows,
						options: triStateOptions,
						onChange: function (v) { setAttributes({ overrideArrows: v }); }
					}),
					el(SelectControl, {
						label: 'Loop',
						value: attrs.overrideLoop,
						options: triStateOptions,
						onChange: function (v) { setAttributes({ overrideLoop: v }); }
					}),
					el(TextControl, {
						label: 'Min Height (px)',
						help: 'Leave empty to use slider default.',
						value: attrs.overrideMinHeight,
						onChange: function (v) { setAttributes({ overrideMinHeight: v }); },
						type: 'number'
					})
				) : null
			);

			// No slider selected — placeholder.
			if (!sliderId) {
				return el(
					Fragment,
					null,
					inspectorControls,
					el(
						Placeholder,
						{
							icon: 'images-alt2',
							label: 'LW Slider',
							instructions: sliders === null
								? 'Loading sliders...'
								: (sliders.length === 0
									? 'No sliders found. Create one first.'
									: 'Select a slider to display.')
						},
						sliders === null
							? el(Spinner)
							: (sliders.length > 0
								? sliderSelector
								: el(
									Button,
									{
										variant: 'primary',
										href: lwSliderBlock.adminUrl + 'post-new.php?post_type=lw-slider',
										target: '_blank'
									},
									'Create Slider'
								)
							)
					)
				);
			}

			// Slider selected — card UI.
			return el(
				Fragment,
				null,
				inspectorControls,
				el(
					'div',
					{ className: 'lw-slider-block-card' },
					el(
						'div',
						{ className: 'lw-slider-block-card__inner' },
						el(
							'div',
							{ className: 'lw-slider-block-card__icon' },
							sliderIcon()
						),
						el(
							'div',
							{ className: 'lw-slider-block-card__info' },
							el('span', { className: 'lw-slider-block-card__label' }, 'LW Slider'),
							el(
								'span',
								{ className: 'lw-slider-block-card__title' },
								selected ? selected.title : 'Slider #' + sliderId
							),
							selected ? el(
								'span',
								{ className: 'lw-slider-block-card__meta' },
								selected.slides + ' slide' + (selected.slides !== 1 ? 's' : '') + '  ·  ID: ' + sliderId
							) : null
						),
						el(
							'div',
							{ className: 'lw-slider-block-card__actions' },
							el(
								Button,
								{
									variant: 'secondary',
									isSmall: true,
									onClick: function () { setAttributes({ sliderId: 0 }); }
								},
								'Change'
							)
						)
					)
				)
			);
		},

		save: function () {
			return null;
		}
	});
})(window.wp);
