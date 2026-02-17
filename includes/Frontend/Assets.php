<?php
/**
 * Frontend asset loader.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Frontend;

/**
 * Conditionally loads frontend CSS and JS only when a slider is present.
 */
final class Assets {

	/**
	 * Whether assets are needed on the current page.
	 *
	 * @var bool
	 */
	private static bool $needed = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'wp_footer', array( $this, 'maybe_enqueue' ), 1 );
	}

	/**
	 * Mark that assets are needed (called by shortcode/block).
	 *
	 * @return void
	 */
	public static function mark_as_needed(): void {
		self::$needed = true;
	}

	/**
	 * Register (but don't enqueue) assets.
	 *
	 * @return void
	 */
	public function register_assets(): void {
		wp_register_style(
			'splide',
			LW_SLIDER_URL . 'assets/vendor/splide/splide.min.css',
			array(),
			'4.1.4'
		);

		wp_register_style(
			'lw-slider',
			LW_SLIDER_URL . 'assets/css/slider.css',
			array( 'splide' ),
			LW_SLIDER_VERSION
		);

		wp_register_script(
			'splide',
			LW_SLIDER_URL . 'assets/vendor/splide/splide.min.js',
			array(),
			'4.1.4',
			true
		);

		wp_register_script(
			'lw-slider',
			LW_SLIDER_URL . 'assets/js/slider.js',
			array( 'splide' ),
			LW_SLIDER_VERSION,
			true
		);
	}

	/**
	 * Enqueue assets only if a slider was rendered.
	 *
	 * @return void
	 */
	public function maybe_enqueue(): void {
		if ( ! self::$needed ) {
			return;
		}

		wp_enqueue_style( 'lw-slider' );
		wp_enqueue_script( 'lw-slider' );
	}
}
