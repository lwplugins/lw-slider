<?php
/**
 * Tests for the slider meta repository.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Data;

use LightweightPlugins\Slider\Data\SliderRepository;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use LightweightPlugins\Slider\Tests\Unit\StubsWordPress;

/**
 * Reads fill defaults, writes keep backslashes.
 */
final class SliderRepositoryTest extends MonkeyTestCase {

	use StubsWordPress;

	protected function setUp(): void {
		parent::setUp();
		$this->stub_wordpress();
		$this->stub_meta_store();
	}

	public function test_missing_meta_reads_as_empty_slides_and_default_settings(): void {
		$this->assertSame( [], SliderRepository::slides( 7 ) );
		$this->assertSame( '400', SliderRepository::settings( 7 )['min_height_desktop'] );
	}

	public function test_slides_are_filled_with_defaults(): void {
		$this->meta[7][ SliderRepository::SLIDES_KEY ] = [ [ 'headline' => 'Old' ], 'broken' ];

		$slides = SliderRepository::slides( 7 );

		$this->assertCount( 1, $slides );
		$this->assertSame( 'Old', $slides[0]['headline'] );
		$this->assertSame( 'image', $slides[0]['bg_type'] );
	}

	public function test_writes_keep_backslashes(): void {
		SliderRepository::save_slides( 7, [ [ 'headline' => 'a\\b' ] ] );
		SliderRepository::save_settings( 7, [ 'custom_class' => 'x' ] );

		$this->assertSame( 'a\\b', $this->meta[7][ SliderRepository::SLIDES_KEY ][0]['headline'] );
		$this->assertSame( 'x', $this->meta[7][ SliderRepository::SETTINGS_KEY ]['custom_class'] );
	}
}
