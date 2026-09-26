<?php
/**
 * Tests for uninstall.php.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit;

use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * Every status (trash included), every site of a network.
 */
final class UninstallTest extends MonkeyTestCase {

	#[RunInSeparateProcess]
	public function test_trashed_sliders_on_every_site_are_deleted(): void {
		define( 'WP_UNINSTALL_PLUGIN', 'lw-slider/lw-slider.php' );
		$deleted  = [];
		$switched = [];

		Functions\when( 'is_multisite' )->justReturn( true );
		Functions\when( 'get_sites' )->justReturn( [ 1, 2 ] );
		Functions\when( 'switch_to_blog' )->alias(
			static function ( $id ) use ( &$switched ) {
				$switched[] = $id;
			}
		);
		Functions\when( 'restore_current_blog' )->justReturn( true );
		Functions\when( 'get_post_stati' )->justReturn( [ 'publish' => 1, 'draft' => 1, 'trash' => 1, 'auto-draft' => 1 ] );
		Functions\when( 'get_posts' )->alias(
			static function ( $args ) use ( &$switched ) {
				\PHPUnit\Framework\Assert::assertSame( [ 'publish', 'draft', 'trash', 'auto-draft' ], $args['post_status'] );
				return [ 10 * end( $switched ) ];
			}
		);
		Functions\when( 'wp_delete_post' )->alias(
			static function ( $id, $force ) use ( &$deleted ) {
				$deleted[] = [ $id, $force ];
			}
		);

		require dirname( __DIR__, 2 ) . '/uninstall.php';

		$this->assertSame( [ 1, 2 ], $switched );
		$this->assertSame( [ [ 10, true ], [ 20, true ] ], $deleted );
	}
}
