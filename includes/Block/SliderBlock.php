<?php
/**
 * Gutenberg block registration.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Block;

use LightweightPlugins\Slider\Admin\AppPage;
use LightweightPlugins\Slider\Frontend\Assets;
use LightweightPlugins\Slider\Frontend\Renderer;
use LightweightPlugins\Slider\Frontend\SliderVisibility;
use LightweightPlugins\Slider\PostType\SliderPostType;

/**
 * Registers the LW Slider Gutenberg block.
 */
final class SliderBlock {

	/**
	 * Editor script handle WordPress generates from block.json.
	 */
	private const EDITOR_HANDLE = 'lw-slider-slider-editor-script';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_route' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'localize_script' ] );
		add_filter( 'block_type_metadata', [ self::class, 'metadata' ] );
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
			[
				'render_callback' => [ $this, 'render' ],
			]
		);
	}

	/**
	 * The block's version is the plugin version, so the editor stylesheet
	 * (versioned from block.json) is fetched again after every update.
	 *
	 * @param array<string, mixed> $metadata Block metadata.
	 * @return array<string, mixed>
	 */
	public static function metadata( array $metadata ): array {
		if ( 'lw-slider/slider' === ( $metadata['name'] ?? '' ) ) {
			$metadata['version'] = LW_SLIDER_VERSION;
		}

		return $metadata;
	}

	/**
	 * Hand the editor script the app URL and its translations.
	 *
	 * @return void
	 */
	public function localize_script(): void {
		wp_localize_script(
			self::EDITOR_HANDLE,
			'lwSliderBlock',
			[
				'appUrl' => AppPage::url(),
			]
		);
		wp_set_script_translations( self::EDITOR_HANDLE, 'lw-slider', LW_SLIDER_PATH . 'languages' );
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
	 * The slider sits in a wrapper with the block supports (wide/full
	 * alignment, extra CSS class, anchor).
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return string
	 */
	public function render( array $attributes ): string {
		$slider_id = absint( $attributes['sliderId'] ?? 0 );

		if ( ! SliderVisibility::is_public( $slider_id ) ) {
			return '';
		}

		$overrides = $this->parse_overrides( $attributes );

		$html = Renderer::render( $slider_id, $overrides );

		if ( '' === $html ) {
			return '';
		}

		Assets::mark_as_needed();

		return '<div ' . get_block_wrapper_attributes() . '>' . $html . '</div>';
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
