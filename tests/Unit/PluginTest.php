<?php
/**
 * Tests for the plugin bootstrap wiring.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Plugin;

/**
 * What the plugin hooks on an admin request.
 */
final class PluginTest extends MonkeyTestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'is_admin' )->justReturn( true );
		Functions\when( 'plugin_basename' )->justReturn( 'lw-slider/lw-slider.php' );
		Functions\when( 'add_shortcode' )->justReturn( true );
	}

	public function test_the_retired_reorder_ajax_action_is_not_registered(): void {
		new Plugin();

		$this->assertFalse( has_action( 'wp_ajax_lw_slider_reorder_slides' ) );
	}
}
