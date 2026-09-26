/**
 * LW Slider block (editor).
 *
 * Choose a slider and optionally override some of its settings for this
 * block. Plain ES5 with wp.* globals (no build step); strings go through
 * wp.i18n, translated from languages/*.json.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;
	var _n = wp.i18n._n;
	var sprintf = wp.i18n.sprintf;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;
	var Placeholder = wp.components.Placeholder;
	var Spinner = wp.components.Spinner;
	var Button = wp.components.Button;
	var ExternalLink = wp.components.ExternalLink;

	var boot = window.lwSliderBlock || {};
	var appUrl = boot.appUrl || '';

	/**
	 * Published sliders from the REST API (null while loading).
	 *
	 * @return {Array|null} Sliders.
	 */
	function useSliders() {
		var state = useState( null );

		useEffect( function () {
			wp.apiFetch( { path: '/lw-slider/v1/sliders' } )
				.then( function ( data ) {
					state[ 1 ]( data );
				} )
				.catch( function () {
					state[ 1 ]( [] );
				} );
		}, [] );

		return state[ 0 ];
	}

	/**
	 * "N slides" text.
	 *
	 * @param {number} count Slides.
	 * @return {string} Text.
	 */
	function slidesText( count ) {
		return sprintf(
			/* translators: %d: number of slides. */
			_n( '%d slide', '%d slides', count, 'lw-slider' ),
			count
		);
	}

	/**
	 * Slider select options.
	 *
	 * @param {Array|null} sliders Sliders.
	 * @return {Array} Options.
	 */
	function sliderOptions( sliders ) {
		var options = [ { label: __( 'Select a slider', 'lw-slider' ), value: 0 } ];

		( sliders || [] ).forEach( function ( s ) {
			options.push( {
				label: sprintf(
					/* translators: 1: slider name, 2: "3 slides", 3: slider ID. */
					__( '%1$s (%2$s, ID %3$d)', 'lw-slider' ),
					s.title || __( '(no title)', 'lw-slider' ),
					slidesText( s.slides ),
					s.id
				),
				value: s.id,
			} );
		} );

		return options;
	}

	/**
	 * Slider data by ID.
	 *
	 * @param {Array|null} sliders Sliders.
	 * @param {number}     id      Slider ID.
	 * @return {Object|null} Slider.
	 */
	function findSlider( sliders, id ) {
		var found = null;
		( sliders || [] ).forEach( function ( s ) {
			if ( s.id === id ) {
				found = s;
			}
		} );
		return found;
	}

	/**
	 * The LW Slider mark (same as the admin), in the brand pink.
	 *
	 * @return {Element} SVG.
	 */
	function sliderMark() {
		return el(
			'svg',
			{ width: 28, height: 28, viewBox: '0 0 24 24', 'aria-hidden': true, focusable: false },
			el( 'path', {
				opacity: 0.4,
				fill: '#e91e63',
				d: 'M4 3h16a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zM7.5 19.8a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4zM16.5 19.8a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4z',
			} ),
			el( 'path', {
				fill: '#e91e63',
				d: 'M9.7 7.3a1 1 0 0 1 0 1.4L7.9 10.5l1.8 1.8a1 1 0 1 1-1.4 1.4l-2.5-2.5a1 1 0 0 1 0-1.4l2.5-2.5a1 1 0 0 1 1.4 0zM14.3 7.3a1 1 0 0 1 1.4 0l2.5 2.5a1 1 0 0 1 0 1.4l-2.5 2.5a1 1 0 1 1-1.4-1.4l1.8-1.8-1.8-1.8a1 1 0 0 1 0-1.4zM12 19.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z',
			} )
		);
	}

	registerBlockType( 'lw-slider/slider', {
		edit: function ( props ) {
			var attrs = props.attributes;
			var sliderId = attrs.sliderId;
			var setAttributes = props.setAttributes;
			var sliders = useSliders();
			var selected = findSlider( sliders, sliderId );
			var blockProps = useBlockProps();

			var triState = [
				{ label: __( 'Slider default', 'lw-slider' ), value: '' },
				{ label: __( 'On', 'lw-slider' ), value: 'on' },
				{ label: __( 'Off', 'lw-slider' ), value: 'off' },
			];

			var override = function ( key, label, options ) {
				return el( SelectControl, {
					__next40pxDefaultSize: true,
					__nextHasNoMarginBottom: true,
					label: label,
					value: attrs[ key ],
					options: options || triState,
					onChange: function ( v ) {
						var next = {};
						next[ key ] = v;
						setAttributes( next );
					},
				} );
			};

			var sliderSelector = el( SelectControl, {
				__next40pxDefaultSize: true,
				__nextHasNoMarginBottom: true,
				label: __( 'Slider', 'lw-slider' ),
				value: sliderId || 0,
				options: sliderOptions( sliders ),
				onChange: function ( value ) {
					setAttributes( { sliderId: parseInt( value, 10 ) || 0 } );
				},
			} );

			var inspector = el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: __( 'Slider', 'lw-slider' ), initialOpen: true },
					sliders === null ? el( Spinner ) : sliderSelector,
					sliderId
						? el(
								'p',
								null,
								el( ExternalLink, { href: appUrl + '#slider/' + sliderId }, __( 'Edit this slider', 'lw-slider' ) )
						  )
						: null
				),
				sliderId
					? el(
							PanelBody,
							{ title: __( 'Override settings', 'lw-slider' ), initialOpen: false },
							el(
								'p',
								{ className: 'components-base-control__help' },
								__( "Override the slider's saved settings for this block only.", 'lw-slider' )
							),
							override( 'overrideAutoplay', __( 'Autoplay', 'lw-slider' ) ),
							override( 'overrideTransition', __( 'Transition', 'lw-slider' ), [
								{ label: __( 'Slider default', 'lw-slider' ), value: '' },
								{ label: __( 'Slide', 'lw-slider' ), value: 'slide' },
								{ label: __( 'Fade', 'lw-slider' ), value: 'fade' },
							] ),
							override( 'overrideDots', __( 'Dots', 'lw-slider' ) ),
							override( 'overrideArrows', __( 'Arrows', 'lw-slider' ) ),
							override( 'overrideLoop', __( 'Loop', 'lw-slider' ) ),
							el( TextControl, {
								__next40pxDefaultSize: true,
								__nextHasNoMarginBottom: true,
								label: __( 'Minimum height on desktop (px)', 'lw-slider' ),
								help: __( 'Leave empty to use the slider setting.', 'lw-slider' ),
								type: 'number',
								value: attrs.overrideMinHeight,
								onChange: function ( v ) {
									setAttributes( { overrideMinHeight: v } );
								},
							} )
					  )
					: null
			);

			if ( ! sliderId ) {
				var instructions = __( 'Select a slider to show.', 'lw-slider' );
				if ( sliders === null ) {
					instructions = __( 'Loading sliders…', 'lw-slider' );
				} else if ( ! sliders.length ) {
					instructions = __( 'There is no published slider yet. Create one first.', 'lw-slider' );
				}

				return el(
					'div',
					blockProps,
					inspector,
					el(
						Placeholder,
						{ icon: sliderMark(), label: 'LW Slider', instructions: instructions },
						sliders === null && el( Spinner ),
						sliders && sliders.length > 0 && sliderSelector,
						sliders && ! sliders.length && el( ExternalLink, { href: appUrl + '#new' }, __( 'Create a slider', 'lw-slider' ) )
					)
				);
			}

			return el(
				'div',
				blockProps,
				inspector,
				el(
					'div',
					{ className: 'lw-slider-block-card' },
					el(
						'div',
						{ className: 'lw-slider-block-card__inner' },
						el( 'div', { className: 'lw-slider-block-card__icon' }, sliderMark() ),
						el(
							'div',
							{ className: 'lw-slider-block-card__info' },
							el( 'span', { className: 'lw-slider-block-card__label' }, 'LW Slider' ),
							el(
								'span',
								{ className: 'lw-slider-block-card__title' },
								selected
									? selected.title || __( '(no title)', 'lw-slider' )
									: sprintf(
											/* translators: %d: slider ID. */
											__( 'Slider %d (not published)', 'lw-slider' ),
											sliderId
									  )
							),
							selected
								? el(
										'span',
										{ className: 'lw-slider-block-card__meta' },
										el( 'span', null, slidesText( selected.slides ) ),
										el(
											'span',
											null,
											sprintf(
												/* translators: %d: slider ID. */
												__( 'ID %d', 'lw-slider' ),
												sliderId
											)
										)
								  )
								: null
						),
						el(
							'div',
							{ className: 'lw-slider-block-card__actions' },
							el(
								Button,
								{
									variant: 'secondary',
									size: 'small',
									onClick: function () {
										setAttributes( { sliderId: 0 } );
									},
								},
								__( 'Change', 'lw-slider' )
							)
						)
					)
				)
			);
		},

		save: function () {
			return null;
		},
	} );
} )( window.wp );
