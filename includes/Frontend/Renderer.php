<?php
/**
 * Slider frontend renderer.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Frontend;

use LightweightPlugins\Slider\Data\Defaults;

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

		printf(
			'<div class="%s splide" id="lw-slider-%s" data-lw-slider=\'%s\' style="min-height:%spx;">',
			esc_attr( $css_class ),
			esc_attr( (string) $post_id ),
			esc_attr( wp_json_encode( $splide_data ) ),
			esc_attr( $settings['min_height_desktop'] )
		);

		echo '<div class="splide__track"><ul class="splide__list">';

		foreach ( $active_slides as $slide ) {
			$slide = wp_parse_args( $slide, Defaults::slide() );
			SlideMarkup::render( $slide, $settings );
		}

		echo '</ul></div></div>';

		self::render_responsive_style( $post_id, $settings );

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

		if ( ! empty( $s['custom_class'] ) ) {
			$classes[] = sanitize_html_class( $s['custom_class'] );
		}

		return implode( ' ', $classes );
	}

	/**
	 * Render responsive CSS for mobile min-height.
	 *
	 * @param int                  $post_id  Slider post ID.
	 * @param array<string, mixed> $settings Slider settings.
	 * @return void
	 */
	private static function render_responsive_style( int $post_id, array $settings ): void {
		printf(
			'<style>#lw-slider-%s{min-height:%spx}@media(max-width:768px){#lw-slider-%s{min-height:%spx}}</style>',
			esc_attr( (string) $post_id ),
			esc_attr( (string) $settings['min_height_desktop'] ),
			esc_attr( (string) $post_id ),
			esc_attr( (string) $settings['min_height_mobile'] )
		);
	}
}
