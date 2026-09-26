<?php
/**
 * Which sliders may show on the site.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Frontend;

use LightweightPlugins\Slider\PostType\SliderPostType;
use WP_Post;

/**
 * Only published sliders without a password render. A slider is not a
 * page a visitor can open, so a password could never be entered: a
 * password-protected slider is treated as not public.
 */
final class SliderVisibility {

	/**
	 * Whether a slider may render publicly.
	 *
	 * @param int $post_id Slider ID.
	 * @return bool
	 */
	public static function is_public( int $post_id ): bool {
		$post = $post_id > 0 ? get_post( $post_id ) : null;

		return $post instanceof WP_Post
			&& SliderPostType::POST_TYPE === $post->post_type
			&& 'publish' === $post->post_status
			&& '' === (string) $post->post_password;
	}
}
