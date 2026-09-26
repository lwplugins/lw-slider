<?php
/**
 * Tests for the shared slide/settings sanitizer.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Data;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\Data\SliderSanitizer;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use LightweightPlugins\Slider\Tests\Unit\StubsWordPress;

/**
 * The sanitizer also runs as the registered meta sanitize callback, so it
 * must accept any input and be idempotent.
 */
final class SliderSanitizerTest extends MonkeyTestCase {

	use StubsWordPress;

	protected function setUp(): void {
		parent::setUp();
		$this->stub_wordpress();
	}

	public function test_non_array_input_gives_empty_slides(): void {
		$this->assertSame( [], SliderSanitizer::slides( 'x' ) );
		$this->assertSame( [], SliderSanitizer::slides( null ) );
	}

	public function test_non_array_items_are_dropped_and_the_list_reindexed(): void {
		$slides = SliderSanitizer::slides( [ 3 => [ 'headline' => 'A' ], 5 => 'junk', 9 => [ 'headline' => 'B' ] ] );

		$this->assertSame( [ 0, 1 ], array_keys( $slides ) );
		$this->assertSame( 'B', $slides[1]['headline'] );
	}

	public function test_slide_sanitizing_is_idempotent(): void {
		$once = SliderSanitizer::slide(
			[
				'active'          => true,
				'bg_color'        => '#abc',
				'overlay_color'   => '#000000',
				'overlay_opacity' => 70,
				'headline'        => 'Hi <em>there</em>',
				'link_url'        => 'https://example.com',
			]
		);

		$this->assertSame( $once, SliderSanitizer::slide( $once ) );
	}

	public function test_settings_sanitizing_is_idempotent_for_the_defaults(): void {
		$once = SliderSanitizer::settings( Defaults::settings() );

		$this->assertSame( $once, SliderSanitizer::settings( $once ) );
		$this->assertSame( '400', $once['min_height_desktop'] );
	}

	public function test_array_values_where_strings_are_expected_are_harmless(): void {
		$slide    = SliderSanitizer::slide( [ 'link_url' => [ 'x' ], 'bg_color' => [ '#fff' ], 'overlay_opacity' => [ 1 ] ] );
		$settings = SliderSanitizer::settings( [ 'custom_class' => [ 'a' ], 'min_height_desktop' => [ 1 ] ] );

		$this->assertSame( '', $slide['link_url'] );
		$this->assertSame( '#f0f0f0', $slide['bg_color'] );
		$this->assertSame( 0, $slide['overlay_opacity'] );
		$this->assertSame( '', $settings['custom_class'] );
		$this->assertSame( '100', $settings['min_height_desktop'] );
	}

	public function test_css_injection_in_colors_is_rejected(): void {
		$slide = SliderSanitizer::slide( [ 'bg_color' => '#000;background:url(//x)', 'overlay_color' => '#fff}body{' ] );

		$this->assertSame( '#f0f0f0', $slide['bg_color'] );
		$this->assertSame( '', $slide['overlay_color'] );
	}
}
