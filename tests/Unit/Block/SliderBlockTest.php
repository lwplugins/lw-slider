<?php
/**
 * Tests for the block render callback.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Block;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Block\SliderBlock;
use LightweightPlugins\Slider\Data\SliderRepository;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use LightweightPlugins\Slider\Tests\Unit\StubsWordPress;
use WP_Post;

/**
 * Block supports reach the markup; overrides apply; nothing for hidden sliders.
 */
final class SliderBlockTest extends MonkeyTestCase {

	use StubsWordPress;

	protected function setUp(): void {
		parent::setUp();
		$this->stub_wordpress();
		$this->stub_meta_store();
		Functions\when( 'get_post' )->alias(
			static fn( $id ) => in_array( $id, [ 31, 32 ], true )
				? new WP_Post( [ 'ID' => $id, 'post_type' => 'lw-slider', 'post_password' => 32 === $id ? 'x' : '' ] )
				: null
		);
		Functions\when( 'get_post_field' )->justReturn( 'Hero' );
		Functions\when( 'wp_strip_all_tags' )->alias( 'strip_tags' );
		Functions\when( 'get_block_wrapper_attributes' )->justReturn( 'class="wp-block-lw-slider-slider alignfull my-extra" id="hero"' );
		Functions\when( 'wp_enqueue_style' )->justReturn( null );
		Functions\when( 'wp_enqueue_script' )->justReturn( null );
		$this->meta[31][ SliderRepository::SLIDES_KEY ] = [ [ 'active' => true ], [ 'active' => true ] ];
		$this->meta[32][ SliderRepository::SLIDES_KEY ] = [ [ 'active' => true ] ];
	}

	public function test_the_slider_sits_in_the_block_wrapper(): void {
		$html = ( new SliderBlock() )->render( [ 'sliderId' => 31 ] );

		$this->assertStringStartsWith( '<div class="wp-block-lw-slider-slider alignfull my-extra" id="hero"><div class="lw-slider ', $html );
	}

	public function test_overrides_apply(): void {
		$html = ( new SliderBlock() )->render( [ 'sliderId' => 31, 'overrideDots' => 'off', 'overrideTransition' => 'fade', 'overrideMinHeight' => '640' ] );

		$this->assertStringContainsString( '&quot;pagination&quot;:false', $html );
		$this->assertStringContainsString( '&quot;type&quot;:&quot;fade&quot;', $html );
		$this->assertStringContainsString( '--lw-slider-min-height:640px;', $html );
	}

	public function test_nothing_for_missing_or_protected_sliders(): void {
		$block = new SliderBlock();

		$this->assertSame( '', $block->render( [ 'sliderId' => 0 ] ) );
		$this->assertSame( '', $block->render( [ 'sliderId' => 32 ] ) );
		$this->assertSame( '', $block->render( [ 'sliderId' => 99 ] ) );
	}

	public function test_the_block_version_follows_the_plugin(): void {
		$this->assertSame( LW_SLIDER_VERSION, SliderBlock::metadata( [ 'name' => 'lw-slider/slider', 'version' => '1.0.0' ] )['version'] );
		$this->assertSame( '2', SliderBlock::metadata( [ 'name' => 'core/image', 'version' => '2' ] )['version'] );
	}

	public function test_the_editor_script_loads_every_wp_global_it_uses(): void {
		$deps   = ( require dirname( __DIR__, 3 ) . '/assets/js/block.asset.php' )['dependencies'];
		$script = (string) file_get_contents( dirname( __DIR__, 3 ) . '/assets/js/block.js' );

		preg_match_all( '/\bwp\.([a-zA-Z]+)\./', $script, $used );

		foreach ( array_unique( $used[1] ) as $global ) {
			$handle = 'wp-' . strtolower( (string) preg_replace( '/(?<!^)[A-Z]/', '-$0', $global ) );
			$this->assertContains( $handle, $deps, 'block.js uses wp.' . $global );
		}
	}
}
