<?php
/**
 * Tests for the slider meta registration.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\PostType;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\PostType\SliderMeta;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use LightweightPlugins\Slider\Tests\Unit\StubsWordPress;

/**
 * Both keys are registered with the shared sanitizer and an edit_post auth
 * check, without a default, and outside the core REST API.
 */
final class SliderMetaTest extends MonkeyTestCase {

	use StubsWordPress;

	/**
	 * Captured register_post_meta() calls: key => args.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private array $registered = [];

	protected function setUp(): void {
		parent::setUp();
		$this->stub_wordpress();
		Functions\when( 'register_post_meta' )->alias(
			function ( $post_type, $key, $args ) {
				$this->assertSame( 'lw-slider', $post_type );
				$this->registered[ $key ] = $args;
				return true;
			}
		);
		SliderMeta::register();
	}

	public function test_both_keys_are_registered_single_without_default(): void {
		$this->assertSame( [ '_lw_slider_slides', '_lw_slider_settings' ], array_keys( $this->registered ) );

		foreach ( $this->registered as $args ) {
			$this->assertTrue( $args['single'] );
			$this->assertArrayNotHasKey( 'default', $args );
			$this->assertFalse( $args['show_in_rest'] );
		}
	}

	public function test_the_sanitize_callbacks_use_the_shared_sanitizer(): void {
		$slides   = call_user_func( $this->registered['_lw_slider_slides']['sanitize_callback'], [ [ 'bg_color' => 'red;x' ] ] );
		$settings = call_user_func( $this->registered['_lw_slider_settings']['sanitize_callback'], 'not an array' );

		$this->assertSame( '#f0f0f0', $slides[0]['bg_color'] );
		$this->assertSame( '400', $settings['min_height_desktop'] );
	}

	public function test_the_auth_callback_checks_edit_post_for_that_user(): void {
		Functions\expect( 'user_can' )->once()->with( 3, 'edit_post', 11 )->andReturn( false );

		$this->assertFalse( call_user_func( $this->registered['_lw_slider_slides']['auth_callback'], true, '_lw_slider_slides', 11, 3 ) );
	}
}
