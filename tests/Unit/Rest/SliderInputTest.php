<?php
/**
 * Tests for the slider request body validation.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Rest;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Rest\Input\SliderInput;
use LightweightPlugins\Slider\Rest\Input\SlideValidator;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use LightweightPlugins\Slider\Tests\Unit\StubsWordPress;
use WP_Post;

/**
 * Errors are keyed by field path; valid bodies give typed values.
 */
final class SliderInputTest extends MonkeyTestCase {

	use StubsWordPress;

	protected function setUp(): void {
		parent::setUp();
		$this->stub_wordpress();
		Functions\when( 'current_user_can' )->justReturn( true );
	}

	public function test_a_valid_body(): void {
		$input = new SliderInput(
			[
				'title'    => 'Home <b>hero</b>',
				'status'   => 'publish',
				'settings' => [ 'autoplay' => true, 'min_height_desktop' => '500' ],
				'slides'   => [ [ 'headline' => 'A', 'overlay_color' => '' ], [] ],
				'modified' => '2026-01-01 00:00:00',
			]
		);

		$this->assertSame( [], $input->errors() );
		$this->assertSame( 'Home hero', $input->get( 'title' ) );
		$this->assertSame( [ 'autoplay' => true, 'min_height_desktop' => 500 ], $input->get( 'settings' ) );
		$this->assertCount( 2, $input->get( 'slides' ) );
		$this->assertSame( 'image', $input->get( 'slides' )[1]['bg_type'] );
		$this->assertSame( '2026-01-01 00:00:00', $input->get( 'modified' ) );
	}

	public function test_errors_are_keyed_by_field_path(): void {
		$input = new SliderInput(
			[
				'colour'   => 'x',
				'settings' => [ 'dots' => 'yes', 'speed' => 3, 'custom_class' => 'a b' ],
				'slides'   => [ [ 'headline' => 'ok' ], [ 'link_url' => 'javascript:x', 'bg_color' => '#12', 'extra' => 1 ], 'nope' ],
			]
		);

		$this->assertSame(
			[ 'colour', 'settings.dots', 'settings.speed', 'settings.custom_class', 'slides.1.link_url', 'slides.1.bg_color', 'slides.1.extra', 'slides.2' ],
			array_keys( $input->errors() )
		);
	}

	public function test_slides_must_be_a_list_and_not_too_long(): void {
		$this->assertArrayHasKey( 'slides', ( new SliderInput( [ 'slides' => [ 'a' => [] ] ] ) )->errors() );
		$this->assertArrayHasKey( 'slides', ( new SliderInput( [ 'slides' => array_fill( 0, SlideValidator::MAX_SLIDES + 1, [] ) ] ) )->errors() );
	}

	public function test_settings_must_be_an_object(): void {
		$this->assertArrayHasKey( 'settings', ( new SliderInput( [ 'settings' => [ true ] ] ) )->errors() );
		$this->assertSame( [], ( new SliderInput( [ 'settings' => [] ] ) )->errors() );
	}

	public function test_publishing_needs_publish_posts(): void {
		Functions\when( 'current_user_can' )->alias( static fn( $cap ) => 'publish_posts' !== $cap );

		$this->assertArrayHasKey( 'status', ( new SliderInput( [ 'status' => 'publish' ] ) )->errors() );
		$this->assertSame( [], ( new SliderInput( [ 'status' => 'draft' ] ) )->errors() );
	}

	public function test_an_unchanged_status_is_accepted_even_if_the_admin_does_not_set_it(): void {
		$current = new WP_Post( [ 'post_status' => 'private' ] );

		$this->assertSame( [], ( new SliderInput( [ 'status' => 'private' ], $current ) )->errors() );
		$this->assertArrayHasKey( 'status', ( new SliderInput( [ 'status' => 'pending' ], $current ) )->errors() );
	}
}
