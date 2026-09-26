<?php
/**
 * Permission callbacks of the admin REST routes.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Rest;

use LightweightPlugins\Slider\PostType\SliderPostType;
use WP_Error;
use WP_Post;
use WP_REST_Request;

/**
 * Core post capabilities, checked per slider. A route for a slider that
 * does not exist (or is not a slider) answers 404 before any capability
 * check, so the IDs of other post types are never probed.
 */
final class SliderPermissions {

	/**
	 * The slider app and the list: users who can edit posts.
	 *
	 * @return bool
	 */
	public static function can_list(): bool {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Publishing a slider.
	 *
	 * @return bool
	 */
	public static function can_publish(): bool {
		return current_user_can( 'publish_posts' );
	}

	/**
	 * Route permission: edit this slider.
	 *
	 * @param WP_REST_Request $request Request with an `id` URL parameter.
	 * @return bool|WP_Error
	 */
	public static function edit( WP_REST_Request $request ) {
		return self::check( $request, 'edit_post' );
	}

	/**
	 * Route permission: trash, delete or restore this slider.
	 *
	 * @param WP_REST_Request $request Request with an `id` URL parameter.
	 * @return bool|WP_Error
	 */
	public static function delete( WP_REST_Request $request ) {
		return self::check( $request, 'delete_post' );
	}

	/**
	 * Route permission: copy this slider (edit it, and create sliders).
	 *
	 * @param WP_REST_Request $request Request with an `id` URL parameter.
	 * @return bool|WP_Error
	 */
	public static function duplicate( WP_REST_Request $request ) {
		$allowed = self::check( $request, 'edit_post' );

		return true === $allowed ? current_user_can( 'edit_posts' ) : $allowed;
	}

	/**
	 * The slider behind a request, or null.
	 *
	 * @param WP_REST_Request $request Request with an `id` URL parameter.
	 * @return WP_Post|null
	 */
	public static function slider( WP_REST_Request $request ): ?WP_Post {
		$post = get_post( absint( $request->get_param( 'id' ) ) );

		return $post instanceof WP_Post && SliderPostType::POST_TYPE === $post->post_type ? $post : null;
	}

	/**
	 * 404 for a missing slider, then the per-post capability.
	 *
	 * @param WP_REST_Request $request    Request.
	 * @param string          $capability Meta capability.
	 * @return bool|WP_Error
	 */
	private static function check( WP_REST_Request $request, string $capability ) {
		if ( ! self::can_list() ) {
			return false;
		}

		$post = self::slider( $request );

		if ( null === $post ) {
			return AdminRoutes::not_found();
		}

		return current_user_can( $capability, $post->ID );
	}
}
