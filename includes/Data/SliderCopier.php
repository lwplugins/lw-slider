<?php
/**
 * Slider duplication.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Data;

use LightweightPlugins\Slider\PostType\SliderPostType;
use WP_Error;
use WP_Post;

/**
 * Creates a draft copy of a slider (title, slides, settings). Callers check
 * the capabilities: edit_post on the source and edit_posts to create.
 */
final class SliderCopier {

	/**
	 * Whether a post can be used as a copy source (a slider, not trashed).
	 *
	 * @param WP_Post|null $post Candidate.
	 * @return bool
	 */
	public static function is_copyable( ?WP_Post $post ): bool {
		return $post instanceof WP_Post
			&& SliderPostType::POST_TYPE === $post->post_type
			&& 'trash' !== $post->post_status;
	}

	/**
	 * Copy a slider into a new draft.
	 *
	 * @param WP_Post $source Source slider.
	 * @return int|WP_Error New post ID, or the insert error.
	 */
	public static function copy( WP_Post $source ) {
		if ( ! self::is_copyable( $source ) ) {
			return new WP_Error( 'lw_slider_not_copyable', __( 'Slider not found.', 'lw-slider' ) );
		}

		$new_id = wp_insert_post(
			[
				'post_title'  => wp_slash( $source->post_title . ' ' . __( '(Copy)', 'lw-slider' ) ),
				'post_type'   => SliderPostType::POST_TYPE,
				'post_status' => 'draft',
			],
			true
		);

		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}

		$new_id = (int) $new_id;

		if ( '' !== get_post_meta( $source->ID, SliderRepository::SLIDES_KEY, true ) ) {
			SliderRepository::save_slides( $new_id, SliderRepository::raw_slides( $source->ID ) );
		}

		$settings = get_post_meta( $source->ID, SliderRepository::SETTINGS_KEY, true );

		if ( is_array( $settings ) && [] !== $settings ) {
			SliderRepository::save_settings( $new_id, $settings );
		}

		return $new_id;
	}
}
