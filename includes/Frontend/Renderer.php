<?php
/**
 * Slider frontend renderer.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Frontend;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\Data\SliderSanitizer;

/**
 * Renders the slider HTML markup for Splide.js.
 */
final class Renderer {

	/**
	 * Render a slider by post ID.
	 *
	 * @param int                  $post_id   Slider post ID.
	 * @param array<string, mixed> $overrides Optional setting overrides.
	 * @return string
	 */
	public static function render( int $post_id, array $overrides = array() ): string {
		$slides = get_post_meta( $post_id, '_lw_slider_slides', true );

		if ( ! is_array( $slides ) || empty( $slides ) ) {
			return '';
		}

		$active_slides = array_filter( $slides, fn( $s ) => ! empty( $s['active'] ) );

		if ( empty( $active_slides ) ) {
			return '';
		}

		$settings = self::get_settings( $post_id );

		if ( ! empty( $overrides ) ) {
			$settings = array_merge( $settings, $overrides );
		}

		$count       = count( $active_slides );
		$splide_data = SplideConfig::build( $settings, $count, self::label( $post_id ) );
		$css_class   = self::build_css_class( $settings );

		ob_start();

		// The heights are custom properties that slider.css turns into
		// min-height: an inline min-height would beat the mobile media query.
		printf(
			'<div class="%s splide" id="%s" data-lw-slider=\'%s\' style="--lw-slider-min-height:%dpx;--lw-slider-min-height-mobile:%dpx;">',
			esc_attr( $css_class ),
			esc_attr( self::element_id( $post_id ) ),
			esc_attr( (string) wp_json_encode( $splide_data ) ),
			(int) self::height( $settings['min_height_desktop'] ),
			(int) self::height( $settings['min_height_mobile'] )
		);

		echo '<div class="splide__track"><ul class="splide__list">';

		self::prime_images( $active_slides );

		foreach ( array_values( $active_slides ) as $index => $slide ) {
			SlideMarkup::render( wp_parse_args( $slide, Defaults::slide() ), $settings, $index );
		}

		echo '</ul></div>';

		if ( SplideConfig::has_autoplay( $settings, $count ) ) {
			self::render_toggle();
		}

		echo '</div>';

		return (string) ob_get_clean();
	}

	/**
	 * Get merged settings.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, mixed>
	 */
	private static function get_settings( int $post_id ): array {
		$settings = get_post_meta( $post_id, '_lw_slider_settings', true );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return wp_parse_args( $settings, Defaults::settings() );
	}

	/**
	 * Load every slide image's post and meta in one query each, instead of
	 * two queries per image.
	 *
	 * @param array<int|string, array<string, mixed>> $slides Active slides.
	 * @return void
	 */
	private static function prime_images( array $slides ): void {
		$ids = array_filter( array_map( static fn( $slide ) => absint( $slide['bg_image_id'] ?? 0 ), $slides ) );

		if ( [] !== $ids && function_exists( '_prime_post_caches' ) ) {
			_prime_post_caches( array_values( array_unique( $ids ) ), false, true );
		}
	}

	/**
	 * Accessible name of the carousel: the slider title.
	 *
	 * @param int $post_id Slider ID.
	 * @return string
	 */
	private static function label( int $post_id ): string {
		$title = trim( wp_strip_all_tags( (string) get_post_field( 'post_title', $post_id, 'raw' ) ) );

		return '' !== $title ? $title : __( 'Slider', 'lw-slider' );
	}

	/**
	 * The pause/play button of an autoplaying slider (WCAG 2.2.2). Splide
	 * finds it by its class, switches its icon and its label.
	 *
	 * @return void
	 */
	private static function render_toggle(): void {
		printf(
			'<button class="splide__toggle lw-slider__toggle" type="button" aria-label="%s">'
			. '<span class="splide__toggle__play"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg></span>'
			. '<span class="splide__toggle__pause"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z" fill="currentColor"/></svg></span>'
			. '</button>',
			esc_attr__( 'Pause autoplay', 'lw-slider' )
		);
	}

	/**
	 * Build the CSS class string.
	 *
	 * @param array<string, mixed> $s Settings.
	 * @return string
	 */
	private static function build_css_class( array $s ): string {
		$classes = array( 'lw-slider' );

		if ( ! empty( $s['use_default_styles'] ) ) {
			$classes[] = 'lw-slider--styled';
		}

		if ( empty( $s['arrows_mobile'] ) ) {
			$classes[] = 'lw-slider--no-arrows-mobile';
		}

		if ( ! empty( $s['hide_on_mobile'] ) ) {
			$classes[] = 'lw-slider--hide-mobile';
		}

		if ( ! empty( $s['custom_class'] ) ) {
			$classes[] = sanitize_html_class( $s['custom_class'] );
		}

		return implode( ' ', $classes );
	}

	/**
	 * HTML id of a slider instance: lw-slider-{ID} for the first one on the
	 * page (the id 1.0 printed, so custom CSS keeps working), a unique
	 * suffixed id for every further copy of the same slider.
	 *
	 * @param int $post_id Slider ID.
	 * @return string
	 */
	private static function element_id( int $post_id ): string {
		static $seen = [];

		$base = 'lw-slider-' . $post_id;

		if ( empty( $seen[ $post_id ] ) ) {
			$seen[ $post_id ] = true;
			return $base;
		}

		return wp_unique_id( $base . '-' );
	}

	/**
	 * A min height in pixels, clamped to the allowed range.
	 *
	 * @param mixed $value Stored or override value.
	 * @return int
	 */
	private static function height( $value ): int {
		return SliderSanitizer::clamp( $value, ...SliderSanitizer::MIN_HEIGHT );
	}
}
