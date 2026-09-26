<?php
/**
 * Tests for the Splide options.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Frontend\SplideConfig;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;

/**
 * Labels Splide can fill in, and autoplay pausing.
 */
final class SplideConfigTest extends MonkeyTestCase {

	public function test_the_slide_label_uses_splides_plain_placeholders(): void {
		Functions\stubTranslationFunctions();

		$this->assertSame( '%s of %s', SplideConfig::i18n()['slideLabel'] );
	}

	public function test_a_translated_slide_label_is_converted_too(): void {
		Functions\when( '__' )->alias( static fn( $text ) => '%1$s of %2$s' === $text ? '%1$s / %2$s' : $text );

		$this->assertSame( '%s / %s', SplideConfig::slide_label() );
	}
}
