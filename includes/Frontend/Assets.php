<?php
/**
 * Frontend asset loader.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Frontend;

use WP_Post;

/**
 * Loads the frontend CSS and JS only where a slider shows.
 *
 * When the viewed post holds the shortcode or the block, both are
 * enqueued up front, so the stylesheet is in <head> (no flash of stacked
 * slides). Any other slider (widget, template, block theme part) enqueues
 * them while rendering: in block themes that still lands in <head>, in
 * classic themes WordPress prints the stylesheet in the footer.
 */
final class Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Registered on init: block themes render templates before wp_head.
		add_action( 'init', [ self::class, 'register_assets' ] );
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_for_content' ] );
	}

	/**
	 * Enqueue the slider assets (called by the shortcode and the block).
	 *
	 * @return void
	 */
	public static function mark_as_needed(): void {
		wp_enqueue_style( 'lw-slider' );
		wp_enqueue_script( 'lw-slider' );
	}

	/**
	 * Register (but don't enqueue) the assets.
	 *
	 * @return void
	 */
	public static function register_assets(): void {
		wp_register_style( 'splide', LW_SLIDER_URL . 'assets/vendor/splide/splide.min.css', [], '4.1.4' );
		wp_register_style( 'lw-slider', LW_SLIDER_URL . 'assets/css/slider.css', [ 'splide' ], LW_SLIDER_VERSION );
		wp_register_script( 'splide', LW_SLIDER_URL . 'assets/vendor/splide/splide.min.js', [], '4.1.4', true );
		wp_register_script( 'lw-slider', LW_SLIDER_URL . 'assets/js/slider.js', [ 'splide' ], LW_SLIDER_VERSION, true );
	}

	/**
	 * Enqueue up front when the viewed post contains a slider.
	 *
	 * @return void
	 */
	public static function enqueue_for_content(): void {
		$post = is_singular() ? get_queried_object() : null;
		$has  = $post instanceof WP_Post && self::content_has_slider( $post );

		/**
		 * Whether this page shows a slider, so its CSS goes into <head>.
		 *
		 * @since 1.1.0
		 *
		 * @param bool $has Detected from the viewed post's shortcode or block.
		 */
		if ( apply_filters( 'lw_slider_page_has_slider', $has ) ) {
			self::mark_as_needed();
		}
	}

	/**
	 * Whether a post's content holds the shortcode or the block.
	 *
	 * @param WP_Post $post Post.
	 * @return bool
	 */
	public static function content_has_slider( WP_Post $post ): bool {
		return has_shortcode( $post->post_content, 'lw_slider' ) || has_block( 'lw-slider/slider', $post );
	}
}
