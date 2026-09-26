<?php
/**
 * Tests for slider duplication.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Data;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Admin\SliderDuplicator;
use LightweightPlugins\Slider\Data\SliderCopier;
use LightweightPlugins\Slider\Data\SliderRepository;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use LightweightPlugins\Slider\Tests\Unit\StubsWordPress;
use WP_Error;
use WP_Post;

/**
 * Duplicating: per-post permission, post type, insert errors, backslashes.
 */
final class SliderCopierTest extends MonkeyTestCase {

	use StubsWordPress;

	protected function setUp(): void {
		parent::setUp();
		$this->stub_wordpress();
		$this->stub_meta_store();
		Functions\when( 'is_wp_error' )->alias( static fn( $thing ) => $thing instanceof WP_Error );
	}

	private function slider( array $fields = [] ): WP_Post {
		return new WP_Post( array_merge( [ 'ID' => 5, 'post_type' => 'lw-slider', 'post_title' => 'Home', 'post_status' => 'publish' ], $fields ) );
	}

	public function test_copy_creates_a_draft_and_copies_meta_with_backslashes(): void {
		$this->meta[5][ SliderRepository::SLIDES_KEY ]   = [ [ 'headline' => 'C:\\dir' ] ];
		$this->meta[5][ SliderRepository::SETTINGS_KEY ] = [ 'loop' => false ];

		Functions\expect( 'wp_insert_post' )
			->once()
			->with(
				[
					'post_title'  => 'Home (Copy)',
					'post_type'   => 'lw-slider',
					'post_status' => 'draft',
				],
				true
			)
			->andReturn( 9 );

		$this->assertSame( 9, SliderCopier::copy( $this->slider() ) );
		$this->assertSame( 'C:\\dir', $this->meta[9][ SliderRepository::SLIDES_KEY ][0]['headline'] );
		$this->assertSame( [ 'loop' => false ], $this->meta[9][ SliderRepository::SETTINGS_KEY ] );
	}

	public function test_an_insert_error_is_returned_and_nothing_is_written(): void {
		$this->meta[5][ SliderRepository::SLIDES_KEY ] = [ [ 'headline' => 'x' ] ];
		Functions\when( 'wp_insert_post' )->justReturn( new WP_Error( 'db', 'fail' ) );

		$result = SliderCopier::copy( $this->slider() );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( [ 5 ], array_keys( $this->meta ) );
	}

	public function test_other_post_types_and_trashed_sliders_are_not_copied(): void {
		Functions\expect( 'wp_insert_post' )->never();

		$this->assertInstanceOf( WP_Error::class, SliderCopier::copy( $this->slider( [ 'post_type' => 'page' ] ) ) );
		$this->assertInstanceOf( WP_Error::class, SliderCopier::copy( $this->slider( [ 'post_status' => 'trash' ] ) ) );
	}

	public function test_duplicating_needs_edit_post_on_the_source(): void {
		Functions\when( 'current_user_can' )->alias( static fn( $cap, $id = null ) => 'edit_posts' === $cap );

		$this->assertFalse( SliderDuplicator::can_duplicate( 5 ) );
	}

	public function test_no_row_action_for_a_slider_the_user_cannot_edit(): void {
		Functions\when( 'current_user_can' )->alias( static fn( $cap, $id = null ) => 'edit_posts' === $cap );

		$actions = ( new SliderDuplicator() )->add_row_action( [ 'edit' => 'x' ], $this->slider() );

		$this->assertSame( [ 'edit' => 'x' ], $actions );
	}

	public function test_row_action_for_an_editable_slider(): void {
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'admin_url' )->alias( static fn( $path = '' ) => 'https://example.test/wp-admin/' . $path );
		Functions\when( 'wp_nonce_url' )->alias( static fn( $url ) => $url . '&_wpnonce=n' );

		$actions = ( new SliderDuplicator() )->add_row_action( [], $this->slider() );

		$this->assertArrayHasKey( 'lw_duplicate', $actions );
	}
}
