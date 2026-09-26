<?php
/**
 * Tests for the slider REST controller.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Rest;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Data\SliderRepository;
use LightweightPlugins\Slider\Rest\AdminRoutes;
use LightweightPlugins\Slider\Rest\SliderPermissions;
use LightweightPlugins\Slider\Rest\SlidersController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * List, create, read and the partial/atomic update with its 409 and 413.
 */
final class SlidersControllerTest extends RestTestCase {

	private SlidersController $controller;

	protected function setUp(): void {
		parent::setUp();
		$this->controller = new SlidersController();
	}

	public function test_permissions_404_for_other_post_types_and_per_slider_edit(): void {
		$this->posts[3] = new \WP_Post( [ 'ID' => 3, 'post_type' => 'page' ] );
		$this->add_slider( 4 );
		$this->denied = [ 'edit_post:4' ];

		$missing = SliderPermissions::edit( $this->request( [ 'id' => 3 ] ) );

		$this->assertInstanceOf( WP_Error::class, $missing );
		$this->assertSame( 404, $missing->get_error_data()['status'] );
		$this->assertFalse( SliderPermissions::edit( $this->request( [ 'id' => 4 ] ) ) );
	}

	public function test_users_without_edit_posts_get_403_not_404(): void {
		$this->denied = [ 'edit_posts' ];

		$this->assertFalse( SliderPermissions::edit( $this->request( [ 'id' => 999 ] ) ) );
		$this->assertFalse( SliderPermissions::can_list() );
	}

	public function test_list_shows_only_editable_sliders(): void {
		$this->add_slider( 1 );
		$this->add_slider( 2, [ 'post_status' => 'trash' ] );
		$this->add_slider( 3 );
		$this->meta[1][ SliderRepository::SLIDES_KEY ] = [
			[ 'active' => true, 'bg_image_id' => 8 ],
			[ 'active' => false ],
		];
		$this->denied = [ 'edit_post:3' ];
		Functions\when( 'get_posts' )->justReturn( [ $this->posts[1], $this->posts[2], $this->posts[3] ] );

		$data = $this->controller->list()->get_data();

		$this->assertSame( [ 1, 2 ], array_column( $data['items'], 'id' ) );
		$this->assertSame( [ 'total' => 2, 'active' => 1 ], $data['items'][0]['slides'] );
		$this->assertSame( 'https://example.test/8-thumbnail.jpg', $data['items'][0]['thumb'] );
		$this->assertSame( '[lw_slider id="1"]', $data['items'][0]['shortcode'] );
	}

	public function test_get_returns_typed_settings_and_images(): void {
		$this->add_slider( 5 );
		$this->meta[5][ SliderRepository::SLIDES_KEY ] = [ [ 'bg_image_id' => 8 ] ];

		$data = $this->controller->get( $this->request( [ 'id' => 5 ] ) )->get_data();

		$this->assertSame( 400, $data['settings']['min_height_desktop'] );
		$this->assertSame( 'image', $data['slides'][0]['bg_type'] );
		$this->assertSame( 'https://example.test/8-medium.jpg', $data['images'][8]['medium'] );
		$this->assertSame( '2026-05-01 10:00:00', $data['modified'] );
	}

	public function test_update_is_partial_merges_settings_and_replaces_slides(): void {
		$this->add_slider( 5 );
		$this->meta[5][ SliderRepository::SETTINGS_KEY ] = [ 'loop' => false, 'dots' => false ];
		Functions\expect( 'wp_update_post' )->once()->andReturn( 5 );

		$response = $this->controller->update(
			$this->request(
				[ 'id' => 5 ],
				[
					'settings' => [ 'dots' => true ],
					'slides'   => [ [ 'headline' => 'New' ] ],
					'modified' => '2026-05-01 10:00:00',
				]
			)
		);

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertFalse( $this->meta[5][ SliderRepository::SETTINGS_KEY ]['loop'] );
		$this->assertTrue( $this->meta[5][ SliderRepository::SETTINGS_KEY ]['dots'] );
		$this->assertSame( '400', $this->meta[5][ SliderRepository::SETTINGS_KEY ]['min_height_desktop'] );
		$this->assertSame( 'New', $this->meta[5][ SliderRepository::SLIDES_KEY ][0]['headline'] );
	}

	public function test_invalid_update_saves_nothing(): void {
		$this->add_slider( 5 );
		Functions\expect( 'wp_update_post' )->never();

		$response = $this->controller->update(
			$this->request( [ 'id' => 5 ], [ 'title' => 'Fine', 'slides' => [ [ 'overlay_opacity' => 101 ] ] ] )
		);

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertSame( 'lw_slider_invalid', $response->get_error_code() );
		$this->assertSame( [ 'slides.0.overlay_opacity' ], array_keys( $response->get_error_data()['fields'] ) );
		$this->assertArrayNotHasKey( 5, $this->meta );
	}

	public function test_a_stale_modified_token_answers_409(): void {
		$this->add_slider( 5 );
		Functions\expect( 'wp_update_post' )->never();

		$response = $this->controller->update( $this->request( [ 'id' => 5 ], [ 'title' => 'X', 'modified' => '2026-04-01 00:00:00' ] ) );

		$this->assertSame( 409, $response->get_error_data()['status'] );
		$this->assertSame( '2026-05-01 10:00:00', $response->get_error_data()['modified'] );
	}

	public function test_an_oversized_body_answers_413(): void {
		$this->add_slider( 5 );
		$request = new WP_REST_Request( [ 'id' => 5 ], 'POST', '', str_repeat( ' ', AdminRoutes::MAX_BYTES + 1 ) );

		$this->assertSame( 413, $this->controller->update( $request )->get_error_data()['status'] );
	}

	public function test_create_makes_a_draft_with_default_settings(): void {
		Functions\expect( 'wp_insert_post' )
			->once()
			->with(
				[
					'post_type'   => 'lw-slider',
					'post_title'  => 'Promo',
					'post_status' => 'draft',
				],
				true
			)
			->andReturnUsing(
				function () {
					$this->add_slider( 20, [ 'post_title' => 'Promo' ] );
					return 20;
				}
			);

		$response = $this->controller->create( $this->request( [], [ 'title' => 'Promo', 'settings' => [ 'autoplay' => true ] ] ) );

		$this->assertSame( 201, $response->status );
		$this->assertTrue( $this->meta[20][ SliderRepository::SETTINGS_KEY ]['autoplay'] );
		$this->assertTrue( $this->meta[20][ SliderRepository::SETTINGS_KEY ]['loop'] );
		$this->assertSame( [], $this->meta[20][ SliderRepository::SLIDES_KEY ] );
	}
}
