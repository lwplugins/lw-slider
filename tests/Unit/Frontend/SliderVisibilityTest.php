<?php
/**
 * Tests for public slider visibility.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Frontend\Shortcode;
use LightweightPlugins\Slider\Frontend\SliderVisibility;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use WP_Post;

/**
 * Published, password-free sliders only.
 */
final class SliderVisibilityTest extends MonkeyTestCase {

	/**
	 * Posts by ID.
	 *
	 * @var array<int, WP_Post>
	 */
	private array $posts = [];

	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'get_post' )->alias( fn( $id ) => $this->posts[ $id ] ?? null );
		$this->posts = [
			1 => new WP_Post( [ 'ID' => 1, 'post_type' => 'lw-slider' ] ),
			2 => new WP_Post( [ 'ID' => 2, 'post_type' => 'lw-slider', 'post_password' => 'secret' ] ),
			3 => new WP_Post( [ 'ID' => 3, 'post_type' => 'lw-slider', 'post_status' => 'draft' ] ),
			4 => new WP_Post( [ 'ID' => 4, 'post_type' => 'page' ] ),
		];
	}

	public function test_only_published_sliders_without_password_are_public(): void {
		$this->assertTrue( SliderVisibility::is_public( 1 ) );
		$this->assertFalse( SliderVisibility::is_public( 2 ) );
		$this->assertFalse( SliderVisibility::is_public( 3 ) );
		$this->assertFalse( SliderVisibility::is_public( 4 ) );
		$this->assertFalse( SliderVisibility::is_public( 0 ) );
	}

	public function test_the_shortcode_prints_nothing_for_a_password_protected_slider(): void {
		Functions\when( 'add_shortcode' )->justReturn( true );
		Functions\when( 'shortcode_atts' )->alias( static fn( $defaults, $atts ) => array_merge( $defaults, (array) $atts ) );
		Functions\when( 'absint' )->alias( static fn( $v ) => abs( (int) $v ) );

		$this->assertSame( '', ( new Shortcode() )->render( [ 'id' => '2' ] ) );
	}
}
