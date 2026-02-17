<?php
/**
 * Gutenberg block registration.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Block;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\Frontend\Assets;
use LightweightPlugins\Slider\Frontend\Renderer;
use LightweightPlugins\Slider\PostType\SliderPostType;

/**
 * Registers the LW Slider Gutenberg block.
 */
final class SliderBlock {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'localize_script' ) );
	}

	/**
	 * Register the block.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			LW_SLIDER_PATH . 'block.json',
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Localize block editor script with admin URL.
	 *
	 * @return void
	 */
	public function localize_script(): void {
		wp_localize_script(
			'lw-slider-slider-editor-script',
			'lwSliderBlock',
			array(
				'adminUrl' => admin_url(),
			)
		);
	}

	/**
	 * Register REST route for slider list.
	 *
	 * @return void
	 */
	public function register_rest_route(): void {
		register_rest_route(
			'lw-slider/v1',
			'/sliders',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_sliders' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * REST callback: return all published sliders.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_sliders(): \WP_REST_Response {
		$posts = get_posts(
			array(
				'post_type'      => SliderPostType::POST_TYPE,
				'posts_per_page' => 100,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$sliders = array();

		foreach ( $posts as $post ) {
			$slides      = get_post_meta( $post->ID, '_lw_slider_slides', true );
			$slide_count = is_array( $slides ) ? count( $slides ) : 0;

			$sliders[] = array(
				'id'     => $post->ID,
				'title'  => $post->post_title,
				'slides' => $slide_count,
			);
		}

		return new \WP_REST_Response( $sliders );
	}

	/**
	 * Server-side render callback.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return string
	 */
	public function render( array $attributes ): string {
		$slider_id = absint( $attributes['sliderId'] ?? 0 );

		if ( ! $slider_id || 'publish' !== get_post_status( $slider_id ) ) {
			return '';
		}

		$overrides = $this->parse_overrides( $attributes );

		Assets::mark_as_needed();

		return Renderer::render( $slider_id, $overrides );
	}

	/**
	 * Parse block-level overrides from attributes.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return array<string, mixed>
	 */
	private function parse_overrides( array $attributes ): array {
		$overrides = array();
		$bool_map  = array(
			'overrideAutoplay' => 'autoplay',
			'overrideDots'     => 'dots',
			'overrideArrows'   => 'arrows',
			'overrideLoop'     => 'loop',
		);

		foreach ( $bool_map as $attr_key => $setting_key ) {
			$val = $attributes[ $attr_key ] ?? '';
			if ( 'on' === $val ) {
				$overrides[ $setting_key ] = true;
			} elseif ( 'off' === $val ) {
				$overrides[ $setting_key ] = false;
			}
		}

		$transition = $attributes['overrideTransition'] ?? '';
		if ( in_array( $transition, array( 'slide', 'fade' ), true ) ) {
			$overrides['transition'] = $transition;
		}

		$min_height = $attributes['overrideMinHeight'] ?? '';
		if ( '' !== $min_height && is_numeric( $min_height ) ) {
			$overrides['min_height_desktop'] = (string) absint( $min_height );
		}

		return $overrides;
	}
}
