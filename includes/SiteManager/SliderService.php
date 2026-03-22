<?php
/**
 * Slider Service for LW Site Manager abilities.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\SiteManager;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\PostType\SliderPostType;

/**
 * Executes Slider abilities for the Site Manager.
 */
final class SliderService {

	/**
	 * List all sliders.
	 *
	 * @param array<string, mixed> $input Input parameters (unused).
	 * @return array<string, mixed>
	 */
	public static function list_sliders( array $input ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by ability callback interface.
		$posts = get_posts(
			array(
				'post_type'      => SliderPostType::POST_TYPE,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$sliders = array();

		foreach ( $posts as $post ) {
			$slides      = get_post_meta( $post->ID, '_lw_slider_slides', true );
			$slide_count = is_array( $slides ) ? count( $slides ) : 0;

			$sliders[] = array(
				'id'          => $post->ID,
				'title'       => $post->post_title,
				'status'      => $post->post_status,
				'slide_count' => $slide_count,
				'shortcode'   => '[lw_slider id="' . $post->ID . '"]',
			);
		}

		return array(
			'success' => true,
			'sliders' => $sliders,
		);
	}

	/**
	 * Get slider details with slides and settings.
	 *
	 * @param array<string, mixed> $input Input parameters.
	 * @return array<string, mixed>|\WP_Error
	 */
	public static function get_slider( array $input ): array|\WP_Error {
		$id = isset( $input['id'] ) ? absint( $input['id'] ) : 0;

		if ( ! $id ) {
			return new \WP_Error(
				'missing_id',
				__( 'Slider ID is required.', 'lw-slider' ),
				array( 'status' => 400 )
			);
		}

		$post = get_post( $id );

		if ( ! $post || SliderPostType::POST_TYPE !== $post->post_type ) {
			return new \WP_Error(
				'not_found',
				__( 'Slider not found.', 'lw-slider' ),
				array( 'status' => 404 )
			);
		}

		$slides_raw = get_post_meta( $post->ID, '_lw_slider_slides', true );
		$slides     = is_array( $slides_raw ) ? $slides_raw : array();

		$settings_raw = get_post_meta( $post->ID, '_lw_slider_settings', true );
		$settings     = wp_parse_args(
			is_array( $settings_raw ) ? $settings_raw : array(),
			Defaults::settings()
		);

		$normalized_slides = array();

		foreach ( $slides as $slide ) {
			$normalized_slides[] = wp_parse_args( $slide, Defaults::slide() );
		}

		return array(
			'success' => true,
			'slider'  => array(
				'id'       => $post->ID,
				'title'    => $post->post_title,
				'status'   => $post->post_status,
				'settings' => $settings,
				'slides'   => $normalized_slides,
			),
		);
	}
}
