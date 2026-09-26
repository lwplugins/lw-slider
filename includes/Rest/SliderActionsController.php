<?php
/**
 * Slider trash / restore / duplicate REST controller.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Rest;

use LightweightPlugins\Slider\Data\SliderCopier;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * DELETE lw-slider/v1/admin/sliders/{id} (trash; ?force=true deletes a
 * trashed slider for good), POST .../{id}/restore and POST .../{id}/duplicate.
 */
final class SliderActionsController {

	/**
	 * Register the routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		// Merged into the GET/POST endpoints of the same route.
		register_rest_route(
			AdminRoutes::NAMESPACE,
			'/admin/sliders/(?P<id>\d+)',
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete' ],
				'permission_callback' => [ SliderPermissions::class, 'delete' ],
			]
		);

		register_rest_route(
			AdminRoutes::NAMESPACE,
			'/admin/sliders/(?P<id>\d+)/restore',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'restore' ],
				'permission_callback' => [ SliderPermissions::class, 'delete' ],
			]
		);

		register_rest_route(
			AdminRoutes::NAMESPACE,
			'/admin/sliders/(?P<id>\d+)/duplicate',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'duplicate' ],
				'permission_callback' => [ SliderPermissions::class, 'duplicate' ],
			]
		);
	}

	/**
	 * Move a slider to the trash, or delete a trashed one for good.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		$post = SliderPermissions::slider( $request );

		if ( null === $post ) {
			return AdminRoutes::not_found();
		}

		if ( rest_sanitize_boolean( $request->get_param( 'force' ) ) ) {
			if ( 'trash' !== $post->post_status ) {
				return AdminRoutes::error( 'lw_slider_not_trashed', __( 'Move the slider to the trash first.', 'lw-slider' ), 400 );
			}

			return wp_delete_post( $post->ID, true )
				? new WP_REST_Response(
					[
						'id'      => $post->ID,
						'deleted' => true,
					]
				)
				: $this->failed();
		}

		return wp_trash_post( $post->ID ) ? $this->item( $post->ID ) : $this->failed();
	}

	/**
	 * Take a slider out of the trash. It gets its previous status back,
	 * except that only users who may publish get a published one back
	 * (the others get a draft, as core does).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function restore( WP_REST_Request $request ) {
		$post = SliderPermissions::slider( $request );

		if ( null === $post ) {
			return AdminRoutes::not_found();
		}

		if ( 'trash' !== $post->post_status ) {
			return AdminRoutes::error( 'lw_slider_not_trashed', __( 'This slider is not in the trash.', 'lw-slider' ), 400 );
		}

		$status = static function ( $new_status, $post_id, $previous ) {
			return is_string( $previous ) && '' !== $previous && ( 'publish' !== $previous || SliderPermissions::can_publish() ) ? $previous : 'draft';
		};

		add_filter( 'wp_untrash_post_status', $status, 10, 3 );
		$restored = wp_untrash_post( $post->ID );
		remove_filter( 'wp_untrash_post_status', $status, 10 );

		return $restored ? $this->item( $post->ID ) : $this->failed();
	}

	/**
	 * Copy a slider into a new draft.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function duplicate( WP_REST_Request $request ) {
		$post = SliderPermissions::slider( $request );

		if ( null === $post ) {
			return AdminRoutes::not_found();
		}

		$new_id = SliderCopier::copy( $post );
		$copy   = is_wp_error( $new_id ) ? null : get_post( $new_id );

		if ( ! $copy instanceof WP_Post ) {
			return $this->failed();
		}

		return new WP_REST_Response( SliderPresenter::full( $copy ), 201 );
	}

	/**
	 * The list row of a slider after an action.
	 *
	 * @param int $id Slider ID.
	 * @return WP_REST_Response|WP_Error
	 */
	private function item( int $id ) {
		$post = get_post( $id );

		return $post instanceof WP_Post ? new WP_REST_Response( SliderPresenter::item( $post ) ) : AdminRoutes::not_found();
	}

	/**
	 * The generic "that did not work" error.
	 *
	 * @return WP_Error
	 */
	private function failed(): WP_Error {
		return AdminRoutes::error( 'lw_slider_action_failed', __( 'That did not work. Reload the page and try again.', 'lw-slider' ), 500 );
	}
}
