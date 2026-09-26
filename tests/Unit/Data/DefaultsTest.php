<?php
/**
 * Tests for the slide and settings defaults.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Data;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;

/**
 * The stored meta shape depends on these keys, so they are pinned here.
 */
final class DefaultsTest extends MonkeyTestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	public function test_slide_keys_are_stable(): void {
		$this->assertSame(
			[ 'title', 'active', 'bg_type', 'bg_image_id', 'bg_color', 'bg_position', 'overlay_color', 'overlay_opacity', 'headline', 'subheadline', 'description', 'link_url', 'link_target', 'cta_mode', 'button_text', 'image_alt' ],
			array_keys( Defaults::slide() )
		);
	}

	public function test_settings_have_seventeen_keys(): void {
		$this->assertCount( 17, Defaults::settings() );
		$this->assertSame( '400', Defaults::settings()['min_height_desktop'] );
		$this->assertSame( '280', Defaults::settings()['min_height_mobile'] );
	}

	public function test_nine_background_positions(): void {
		$this->assertCount( 9, Defaults::bg_positions() );
		$this->assertArrayHasKey( 'center center', Defaults::bg_positions() );
	}

	public function test_two_transitions(): void {
		$this->assertSame( [ 'slide', 'fade' ], array_keys( Defaults::transitions() ) );
	}
}
