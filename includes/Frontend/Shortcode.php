<?php
/**
 * Slider shortcode.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Frontend;

/**
 * Registers and handles the [lw_slider] shortcode.
 */
final class Shortcode {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'lw_slider', array( $this, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ): string {
		$atts = shortcode_atts(
			array( 'id' => 0 ),
			$atts,
			'lw_slider'
		);

		$id = absint( $atts['id'] );

		if ( ! $id || 'publish' !== get_post_status( $id ) ) {
			return '';
		}

		Assets::mark_as_needed();

		return Renderer::render( $id );
	}
}
