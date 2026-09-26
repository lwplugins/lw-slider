<?php
/**
 * Tests for the strict REST field rules.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Rest;

use LightweightPlugins\Slider\Rest\Input\Rules;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use LightweightPlugins\Slider\Tests\Unit\StubsWordPress;

/**
 * Each rule rejects what it cannot accept and normalizes what it can.
 */
final class RulesTest extends MonkeyTestCase {

	use StubsWordPress;

	protected function setUp(): void {
		parent::setUp();
		$this->stub_wordpress();
	}

	public function test_bool_accepts_only_json_booleans(): void {
		$this->assertNull( Rules::bool( false, $clean ) );
		$this->assertFalse( $clean );
		$this->assertNotNull( Rules::bool( 1, $clean ) );
		$this->assertNotNull( Rules::bool( 'true', $clean ) );
	}

	public function test_int_accepts_numbers_and_digit_strings_in_range(): void {
		$this->assertNull( Rules::int( '250', 100, 1200, $clean ) );
		$this->assertSame( 250, $clean );
		$this->assertNotNull( Rules::int( 99, 100, 1200, $clean ) );
		$this->assertNotNull( Rules::int( '12px', 100, 1200, $clean ) );
		$this->assertNotNull( Rules::int( 1.5, 0, 10, $clean ) );
	}

	public function test_hex_colors(): void {
		$this->assertNull( Rules::hex( '#AbC', false, $clean ) );
		$this->assertNull( Rules::hex( '', true, $clean ) );
		$this->assertSame( '', $clean );
		$this->assertNotNull( Rules::hex( '', false, $clean ) );
		$this->assertNotNull( Rules::hex( '#000;x:y', true, $clean ) );
		$this->assertNotNull( Rules::hex( 'red', true, $clean ) );
	}

	public function test_urls(): void {
		$this->assertNull( Rules::url( ' https://example.com/x ', $clean ) );
		$this->assertSame( 'https://example.com/x', $clean );
		$this->assertNull( Rules::url( '/shop', $clean ) );
		$this->assertNull( Rules::url( '', $clean ) );
		$this->assertSame( '', $clean );
		$this->assertNotNull( Rules::url( 'javascript:alert(1)', $clean ) );
		$this->assertNotNull( Rules::url( [ 'x' ], $clean ) );
	}

	public function test_text_length(): void {
		$this->assertNull( Rules::text( str_repeat( 'á', 5 ), 5, $clean ) );
		$this->assertNotNull( Rules::text( str_repeat( 'a', 6 ), 5, $clean ) );
		$this->assertNotNull( Rules::text( 5, 5, $clean ) );
	}

	public function test_css_class_is_a_single_class_name(): void {
		$this->assertNull( Rules::css_class( 'hero-slider_2', $clean ) );
		$this->assertNull( Rules::css_class( '', $clean ) );
		$this->assertNotNull( Rules::css_class( 'two classes', $clean ) );
		$this->assertNotNull( Rules::css_class( 'x"y', $clean ) );
	}

	public function test_choice(): void {
		$this->assertNull( Rules::choice( 'fade', [ 'slide', 'fade' ], $clean ) );
		$this->assertNotNull( Rules::choice( 'zoom', [ 'slide', 'fade' ], $clean ) );
	}
}
