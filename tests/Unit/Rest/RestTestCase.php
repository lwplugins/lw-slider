<?php
/**
 * Shared setup of the REST controller tests.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Rest;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use LightweightPlugins\Slider\Tests\Unit\StubsWordPress;
use WP_Error;
use WP_Post;
use WP_REST_Request;

/**
 * An in-memory post table, a meta store and capability switches.
 */
abstract class RestTestCase extends MonkeyTestCase {

	use StubsWordPress;

	/**
	 * Posts by ID.
	 *
	 * @var array<int, WP_Post>
	 */
	protected array $posts = [];

	/**
	 * Capabilities the user lacks: "cap" or "cap:post_id".
	 *
	 * @var array<int, string>
	 */
	protected array $denied = [];

	protected function setUp(): void {
		parent::setUp();
		$this->stub_wordpress();
		$this->stub_meta_store();

		Functions\when( 'is_wp_error' )->alias( static fn( $thing ) => $thing instanceof WP_Error );
		Functions\when( 'get_post' )->alias( fn( $id ) => $this->posts[ (int) $id ] ?? null );
		Functions\when( 'current_user_can' )->alias(
			fn( $cap, $id = null ) => ! in_array( $cap, $this->denied, true ) && ! in_array( $cap . ':' . $id, $this->denied, true )
		);
		Functions\when( 'wp_get_attachment_image_url' )->alias( static fn( $id, $size ) => 'https://example.test/' . $id . '-' . $size . '.jpg' );
		Functions\when( 'rest_sanitize_boolean' )->alias( static fn( $value ) => in_array( $value, [ true, 'true', '1', 1 ], true ) );
	}

	/**
	 * Add a slider to the post table.
	 *
	 * @param int                  $id     ID.
	 * @param array<string, mixed> $fields Fields.
	 * @return WP_Post
	 */
	protected function add_slider( int $id, array $fields = [] ): WP_Post {
		$this->posts[ $id ] = new WP_Post(
			array_merge(
				[
					'ID'                => $id,
					'post_type'         => 'lw-slider',
					'post_title'        => 'Slider ' . $id,
					'post_status'       => 'draft',
					'post_modified_gmt' => '2026-05-01 10:00:00',
				],
				$fields
			)
		);

		return $this->posts[ $id ];
	}

	/**
	 * A request with URL params and a JSON body.
	 *
	 * @param array<string, mixed> $params URL/query parameters.
	 * @param array<string, mixed> $body   JSON body.
	 * @return WP_REST_Request
	 */
	protected function request( array $params = [], array $body = [] ): WP_REST_Request {
		return new WP_REST_Request( $params, 'POST', '', (string) json_encode( $body ) );
	}
}
