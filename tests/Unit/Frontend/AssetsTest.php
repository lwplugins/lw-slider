<?php
/**
 * Tests for the frontend asset loading.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Frontend\Assets;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use WP_Post;

/**
 * Up front (head) only on posts with a slider; never on other pages.
 */
final class AssetsTest extends MonkeyTestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'has_shortcode' )->alias( static fn( $content, $tag ) => str_contains( $content, '[' . $tag ) );
		Functions\when( 'has_block' )->alias( static fn( $name, $post ) => str_contains( $post->post_content, '<!-- wp:' . $name ) );
	}

	public function test_a_post_with_the_shortcode_or_block_enqueues_up_front(): void {
		Functions\when( 'is_singular' )->justReturn( true );
		Functions\when( 'get_queried_object' )->justReturn( new WP_Post( [ 'post_content' => '<!-- wp:lw-slider/slider {"sliderId":3} /-->' ] ) );
		Functions\expect( 'wp_enqueue_style' )->once()->with( 'lw-slider' );
		Functions\expect( 'wp_enqueue_script' )->once()->with( 'lw-slider' );

		Assets::enqueue_for_content();
	}

	public function test_other_pages_load_nothing(): void {
		Functions\when( 'is_singular' )->justReturn( true );
		Functions\when( 'get_queried_object' )->justReturn( new WP_Post( [ 'post_content' => 'Hello' ] ) );
		Functions\expect( 'wp_enqueue_style' )->never();

		Assets::enqueue_for_content();

		Functions\when( 'is_singular' )->justReturn( false );
		Assets::enqueue_for_content();
	}

	public function test_shortcode_detection(): void {
		$this->assertTrue( Assets::content_has_slider( new WP_Post( [ 'post_content' => 'a [lw_slider id="2"] b' ] ) ) );
	}
}
