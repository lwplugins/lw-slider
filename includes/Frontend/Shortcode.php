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
		add_shortcode( 'lw_slider', [ $this, 'render' ] );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ): string {
		$atts = shortcode_atts(
			[ 'id' => 0 ],
			$atts,
			'lw_slider'
		);

		$id = absint( $atts['id'] );

		if ( ! SliderVisibility::is_public( $id ) ) {
			return '';
		}

		Assets::mark_as_needed();

		return Renderer::render( $id );
	}
}
