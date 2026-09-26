<?php
/**
 * Tests for the classic screen redirects.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Admin\AppPage;
use LightweightPlugins\Slider\Admin\ClassicRedirect;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;

/**
 * Plain GET page loads go to the app; list actions and POSTs stay.
 */
final class ClassicRedirectTest extends MonkeyTestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'absint' )->alias( static fn( $v ) => abs( (int) $v ) );
		Functions\when( 'admin_url' )->alias( static fn( $path = '' ) => 'https://example.test/wp-admin/' . $path );
	}

	public function test_the_slider_list_goes_to_the_app(): void {
		$this->assertSame( 'sliders', ClassicRedirect::list_target( [ 'post_type' => 'lw-slider' ], 'GET' ) );
		$this->assertSame( 'trash', ClassicRedirect::list_target( [ 'post_type' => 'lw-slider', 'post_status' => 'trash' ], 'GET' ) );
		$this->assertSame( 'sliders', ClassicRedirect::list_target( [ 'post_type' => 'lw-slider', 'action' => '-1' ], 'GET' ) );
	}

	public function test_list_actions_other_types_and_posts_stay(): void {
		$this->assertNull( ClassicRedirect::list_target( [ 'post_type' => 'lw-slider', 'action' => 'untrash' ], 'GET' ) );
		$this->assertNull( ClassicRedirect::list_target( [ 'post_type' => 'lw-slider', 'action2' => 'trash' ], 'GET' ) );
		$this->assertNull( ClassicRedirect::list_target( [ 'post_type' => 'page' ], 'GET' ) );
		$this->assertNull( ClassicRedirect::list_target( [ 'post_type' => 'lw-slider' ], 'POST' ) );
	}

	public function test_edit_maps_the_post_id(): void {
		$this->assertSame( 'slider/12', ClassicRedirect::edit_target( [ 'action' => 'edit', 'post' => '12' ], 'GET', 'lw-slider' ) );
		$this->assertNull( ClassicRedirect::edit_target( [ 'action' => 'trash', 'post' => '12' ], 'GET', 'lw-slider' ) );
		$this->assertNull( ClassicRedirect::edit_target( [ 'action' => 'edit', 'post' => '12' ], 'POST', 'lw-slider' ) );
		$this->assertNull( ClassicRedirect::edit_target( [ 'action' => 'edit', 'post' => '12' ], 'GET', 'post' ) );
	}

	public function test_slider_edit_links_point_at_the_app(): void {
		Functions\when( 'get_post_type' )->alias( static fn( $id ) => 12 === $id ? 'lw-slider' : 'post' );
		$redirect = new ClassicRedirect();

		$this->assertSame( 'https://example.test/wp-admin/admin.php?page=lw-slider#slider/12', $redirect->edit_link( 'x', 12 ) );
		$this->assertSame( 'y', $redirect->edit_link( 'y', 13 ) );
		$this->assertSame( 'https://example.test/wp-admin/admin.php?page=lw-slider', AppPage::url() );
	}
}
