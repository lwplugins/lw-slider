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

		$splide_data = self::build_splide_config( $settings, count( $active_slides ) );
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

		foreach ( $active_slides as $slide ) {
			$slide = wp_parse_args( $slide, Defaults::slide() );
			SlideMarkup::render( $slide, $settings );
		}

		echo '</ul></div></div>';

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
	 * Build Splide.js configuration from settings.
	 *
	 * @param array<string, mixed> $s            Settings.
	 * @param int                  $slide_count  Number of active slides.
	 * @return array<string, mixed>
	 */
	private static function build_splide_config( array $s, int $slide_count ): array {
		$config = array(
			'type'       => ! empty( $s['loop'] ) ? 'loop' : 'slide',
			'pagination' => ! empty( $s['dots'] ) && $slide_count > 1,
			'arrows'     => ! empty( $s['arrows'] ) && $slide_count > 1,
			'drag'       => ! empty( $s['swipe'] ),
			'keyboard'   => ! empty( $s['keyboard'] ) ? 'global' : false,
		);

		if ( 'fade' === $s['transition'] ) {
			$config['type']   = 'fade';
			$config['rewind'] = true;
		}

		if ( ! empty( $s['autoplay'] ) ) {
			$config['autoplay']     = true;
			$config['interval']     = (int) $s['autoplay_delay'];
			$config['pauseOnHover'] = ! empty( $s['pause_on_hover'] );
		}

		return $config;
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
