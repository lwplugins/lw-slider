<?php
/**
 * Tests for the slider trash/restore/duplicate REST controller.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Rest;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Rest\SliderActionsController;
use LightweightPlugins\Slider\Rest\SliderPermissions;
use WP_Error;

/**
 * Trash first, delete only from the trash, restore, duplicate.
 */
final class SliderActionsControllerTest extends RestTestCase {

	private SliderActionsController $controller;

	protected function setUp(): void {
		parent::setUp();
		$this->controller = new SliderActionsController();
	}

	public function test_delete_moves_to_trash(): void {
		$this->add_slider( 5 );
		Functions\expect( 'wp_trash_post' )->once()->with( 5 )->andReturnUsing(
			function () {
				$this->posts[5]->post_status = 'trash';
				return $this->posts[5];
			}
		);

		$data = $this->controller->delete( $this->request( [ 'id' => 5 ] ) )->get_data();

		$this->assertSame( 'trash', $data['status'] );
	}

	public function test_force_delete_needs_a_trashed_slider(): void {
		$this->add_slider( 5 );
		Functions\expect( 'wp_delete_post' )->never();

		$response = $this->controller->delete( $this->request( [ 'id' => 5, 'force' => 'true' ] ) );

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertSame( 400, $response->get_error_data()['status'] );
	}

	public function test_force_delete_of_a_trashed_slider(): void {
		$this->add_slider( 5, [ 'post_status' => 'trash' ] );
		Functions\expect( 'wp_delete_post' )->once()->with( 5, true )->andReturn( $this->posts[5] );

		$this->assertSame( [ 'id' => 5, 'deleted' => true ], $this->controller->delete( $this->request( [ 'id' => 5, 'force' => true ] ) )->get_data() );
	}

	public function test_delete_permission_is_per_slider(): void {
		$this->add_slider( 5 );
		$this->denied = [ 'delete_post:5' ];

		$this->assertFalse( SliderPermissions::delete( $this->request( [ 'id' => 5 ] ) ) );
	}

	public function test_restore_only_from_the_trash(): void {
		$this->add_slider( 5 );

		$this->assertSame( 400, $this->controller->restore( $this->request( [ 'id' => 5 ] ) )->get_error_data()['status'] );
	}

	public function test_restore_gives_back_the_previous_status_to_publishers(): void {
		$this->add_slider( 5, [ 'post_status' => 'trash' ] );
		$filter = null;
		Filters\expectAdded( 'wp_untrash_post_status' )->once()->whenHappen(
			function ( $callback ) use ( &$filter ) {
				$filter = $callback;
			}
		);
		Functions\expect( 'wp_untrash_post' )->once()->andReturnUsing(
			function ( $id ) use ( &$filter ) {
				$this->posts[5]->post_status = $filter( 'draft', $id, 'publish' );
				return $this->posts[5];
			}
		);

		$this->assertSame( 'publish', $this->controller->restore( $this->request( [ 'id' => 5 ] ) )->get_data()['status'] );

		$this->posts[5]->post_status = 'trash';
		$this->denied                = [ 'publish_posts' ];
		$this->assertSame( 'draft', $filter( 'draft', 5, 'publish' ) );
	}

	public function test_duplicate_needs_edit_post_on_the_source_and_answers_201(): void {
		$this->add_slider( 5 );
		$this->denied = [ 'edit_post:5' ];
		$this->assertFalse( SliderPermissions::duplicate( $this->request( [ 'id' => 5 ] ) ) );

		$this->denied = [];
		Functions\when( 'wp_insert_post' )->alias(
			function () {
				$this->add_slider( 6, [ 'post_title' => 'Slider 5 (Copy)' ] );
				return 6;
			}
		);

		$response = $this->controller->duplicate( $this->request( [ 'id' => 5 ] ) );

		$this->assertSame( 201, $response->status );
		$this->assertSame( 6, $response->get_data()['id'] );
	}
}
