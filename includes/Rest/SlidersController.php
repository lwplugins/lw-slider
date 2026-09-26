<?php
/**
 * Slider list / create / read / update REST controller.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Rest;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\Data\SliderRepository;
use LightweightPlugins\Slider\Data\SliderSanitizer;
use LightweightPlugins\Slider\PostType\SliderPostType;
use LightweightPlugins\Slider\Rest\Input\SliderInput;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * GET/POST lw-slider/v1/admin/sliders and GET/POST lw-slider/v1/admin/sliders/{id}.
 *
 * Updates are partial and atomic: only the sent keys change (settings are
 * merged, slides replace the stored list) and nothing is written while any
 * field is invalid. A stale `modified` token answers 409.
 */
final class SlidersController {

	/**
	 * Statuses listed in the admin (trash included, for the Trash view).
	 */
	private const LIST_STATUSES = [ 'publish', 'draft', 'pending', 'private', 'future', 'trash' ];

	/**
	 * Register the routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			AdminRoutes::NAMESPACE,
			'/admin/sliders',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'list' ],
					'permission_callback' => [ SliderPermissions::class, 'can_list' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create' ],
					'permission_callback' => [ SliderPermissions::class, 'can_list' ],
				],
			]
		);

		register_rest_route(
			AdminRoutes::NAMESPACE,
			'/admin/sliders/(?P<id>\d+)',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get' ],
					'permission_callback' => [ SliderPermissions::class, 'edit' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update' ],
					'permission_callback' => [ SliderPermissions::class, 'edit' ],
				],
			]
		);
	}

	/**
	 * Every slider the user may edit.
	 *
	 * @return WP_REST_Response
	 */
	public function list(): WP_REST_Response {
		$posts = get_posts(
			[
				'post_type'        => SliderPostType::POST_TYPE,
				'post_status'      => self::LIST_STATUSES,
				'posts_per_page'   => -1,
				'orderby'          => 'modified',
				'order'            => 'DESC',
				'suppress_filters' => false,
			]
		);

		$items = [];

		foreach ( $posts as $post ) {
			if ( current_user_can( 'edit_post', $post->ID ) ) {
				$items[] = SliderPresenter::item( $post );
			}
		}

		return new WP_REST_Response(
			[
				'items' => $items,
				'meta'  => [ 'can_publish' => SliderPermissions::can_publish() ],
			]
		);
	}

	/**
	 * Create a slider (a draft unless status says otherwise).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create( WP_REST_Request $request ) {
		$too_large = AdminRoutes::too_large( $request );

		if ( null !== $too_large ) {
			return $too_large;
		}

		$input = new SliderInput( AdminRoutes::body( $request ) );

		if ( [] !== $input->errors() ) {
			return AdminRoutes::invalid( $input->errors() );
		}

		$title = $input->has( 'title' ) ? (string) $input->get( 'title' ) : '';
		$id    = wp_insert_post(
			[
				'post_type'   => SliderPostType::POST_TYPE,
				'post_title'  => wp_slash( '' !== $title ? $title : __( 'New slider', 'lw-slider' ) ),
				'post_status' => $input->has( 'status' ) ? (string) $input->get( 'status' ) : 'draft',
			],
			true
		);

		if ( is_wp_error( $id ) ) {
			return AdminRoutes::error( 'lw_slider_save_failed', __( 'The slider could not be saved.', 'lw-slider' ), 500 );
		}

		$id = (int) $id;

		SliderRepository::save_settings( $id, SliderSanitizer::settings( array_merge( Defaults::settings(), (array) $input->get( 'settings' ) ) ) );
		SliderRepository::save_slides( $id, SliderSanitizer::slides( (array) $input->get( 'slides' ) ) );

		return $this->respond( $id, 201 );
	}

	/**
	 * One slider for the editor.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get( WP_REST_Request $request ) {
		$post = SliderPermissions::slider( $request );

		return null === $post ? AdminRoutes::not_found() : new WP_REST_Response( SliderPresenter::full( $post ) );
	}

	/**
	 * Partial, atomic update.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update( WP_REST_Request $request ) {
		$too_large = AdminRoutes::too_large( $request );

		if ( null !== $too_large ) {
			return $too_large;
		}

		$post = SliderPermissions::slider( $request );

		if ( null === $post ) {
			return AdminRoutes::not_found();
		}

		$input = new SliderInput( AdminRoutes::body( $request ), $post );

		if ( [] !== $input->errors() ) {
			return AdminRoutes::invalid( $input->errors() );
		}

		if ( $input->has( 'modified' ) && $input->get( 'modified' ) !== $post->post_modified_gmt ) {
			return AdminRoutes::error(
				'lw_slider_conflict',
				__( 'This slider was changed in another window or by someone else. Reload it to see the latest version.', 'lw-slider' ),
				409,
				[ 'modified' => $post->post_modified_gmt ]
			);
		}

		$saved = $this->save_post( $post, $input );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		if ( $input->has( 'slides' ) ) {
			SliderRepository::save_slides( $post->ID, SliderSanitizer::slides( (array) $input->get( 'slides' ) ) );
		}

		if ( $input->has( 'settings' ) ) {
			$merged = array_merge( SliderRepository::settings( $post->ID ), (array) $input->get( 'settings' ) );
			SliderRepository::save_settings( $post->ID, SliderSanitizer::settings( $merged ) );
		}

		return $this->respond( $post->ID );
	}

	/**
	 * Update title/status. Always runs, so every save moves the
	 * `modified` token forward.
	 *
	 * @param WP_Post     $post  Slider.
	 * @param SliderInput $input Validated input.
	 * @return true|WP_Error
	 */
	private function save_post( WP_Post $post, SliderInput $input ) {
		$args = [ 'ID' => $post->ID ];

		if ( $input->has( 'title' ) ) {
			$args['post_title'] = wp_slash( (string) $input->get( 'title' ) );
		}

		if ( $input->has( 'status' ) ) {
			$args['post_status'] = (string) $input->get( 'status' );
		}

		$result = wp_update_post( $args, true );

		if ( is_wp_error( $result ) ) {
			return AdminRoutes::error( 'lw_slider_save_failed', __( 'The slider could not be saved.', 'lw-slider' ), 500 );
		}

		return true;
	}

	/**
	 * The fresh editor payload of a slider.
	 *
	 * @param int $id     Slider ID.
	 * @param int $status HTTP status.
	 * @return WP_REST_Response|WP_Error
	 */
	private function respond( int $id, int $status = 200 ) {
		$post = get_post( $id );

		if ( ! $post instanceof WP_Post ) {
			return AdminRoutes::not_found();
		}

		return new WP_REST_Response( SliderPresenter::full( $post ), $status );
	}
}
