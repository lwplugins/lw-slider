<?php
/**
 * Characterization tests for the classic slider save handler.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Admin\SliderSaveHandler;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use LightweightPlugins\Slider\Tests\Unit\StubsWordPress;

/**
 * Pins what the metabox form stores in _lw_slider_slides and
 * _lw_slider_settings, so the sanitizing can move into a shared class
 * without changing a single stored value.
 */
final class SliderSaveHandlerTest extends MonkeyTestCase {

	use StubsWordPress;

	private const POST_ID = 42;

	protected function setUp(): void {
		parent::setUp();
		$this->stub_wordpress();
		$this->stub_meta_store();
		$_POST = [];
	}

	protected function tearDown(): void {
		$_POST = [];
		parent::tearDown();
	}

	/**
	 * Run the handler with a posted form (slashed like WordPress does).
	 *
	 * @param array<string, mixed> $post    Form fields.
	 * @param bool                 $can     Whether the user may edit the post.
	 * @param bool                 $nonce   Whether the nonce is valid.
	 */
	private function save( array $post, bool $can = true, bool $nonce = true ): void {
		Functions\when( 'current_user_can' )->justReturn( $can );
		Functions\when( 'wp_verify_nonce' )->justReturn( $nonce ? 1 : false );

		$_POST = wp_slash( array_merge( [ 'lw_slider_nonce' => 'abc' ], $post ) );

		( new SliderSaveHandler() )->save( self::POST_ID );
	}

	public function test_nothing_is_saved_without_a_valid_nonce(): void {
		$this->save( [ 'lw_slider_slides' => [ [ 'headline' => 'X' ] ] ], true, false );

		$this->assertSame( [], $this->meta );
	}

	public function test_nothing_is_saved_without_edit_permission(): void {
		$this->save( [ 'lw_slider_slides' => [ [ 'headline' => 'X' ] ] ], false );

		$this->assertSame( [], $this->meta );
	}

	public function test_no_posted_slides_stores_an_empty_list_and_leaves_settings(): void {
		$this->save( [] );

		$this->assertSame( [ '_lw_slider_slides' => [] ], $this->meta[ self::POST_ID ] );
	}

	public function test_a_full_valid_slide_is_stored_as_typed_values(): void {
		$this->save(
			[
				'lw_slider_slides' => [
					[
						'active'          => '1',
						'bg_type'         => 'color',
						'bg_image_id'     => '17',
						'bg_color'        => '#112233',
						'bg_position'     => 'left top',
						'overlay_color'   => '#000',
						'overlay_opacity' => '35',
						'headline'        => "O'Reilly <b>bold</b>",
						'subheadline'     => 'Sub',
						'description'     => "Line 1\nLine 2",
						'link_url'        => 'https://example.com/a b',
						'link_target'     => '_blank',
						'cta_mode'        => 'button',
						'button_text'     => 'Go',
						'image_alt'       => 'Alt',
					],
				],
			]
		);

		$this->assertSame(
			[
				[
					'title'           => '',
					'active'          => true,
					'bg_type'         => 'color',
					'bg_image_id'     => 17,
					'bg_color'        => '#112233',
					'bg_position'     => 'left top',
					'overlay_color'   => '#000',
					'overlay_opacity' => 35,
					'headline'        => "O'Reilly bold",
					'subheadline'     => 'Sub',
					'description'     => "Line 1\nLine 2",
					'link_url'        => 'https://example.com/a%20b',
					'link_target'     => '_blank',
					'cta_mode'        => 'button',
					'button_text'     => 'Go',
					'image_alt'       => 'Alt',
				],
			],
			$this->meta[ self::POST_ID ]['_lw_slider_slides']
		);
	}

	public function test_invalid_slide_values_fall_back_to_defaults(): void {
		$this->save(
			[
				'lw_slider_slides' => [
					[
						'bg_type'         => 'video',
						'bg_image_id'     => '-9',
						'bg_color'        => 'red; background:url(x)',
						'bg_position'     => 'middle',
						'overlay_color'   => 'nope',
						'overlay_opacity' => '150',
						'link_url'        => 'javascript:alert(1)',
						'link_target'     => '_top',
						'cta_mode'        => 'popup',
					],
					[
						'overlay_opacity' => '-5',
					],
				],
			]
		);

		$slides = $this->meta[ self::POST_ID ]['_lw_slider_slides'];

		$this->assertFalse( $slides[0]['active'] );
		$this->assertSame( 'image', $slides[0]['bg_type'] );
		$this->assertSame( 9, $slides[0]['bg_image_id'] );
		$this->assertSame( '#f0f0f0', $slides[0]['bg_color'] );
		$this->assertSame( 'center center', $slides[0]['bg_position'] );
		$this->assertSame( '', $slides[0]['overlay_color'] );
		$this->assertSame( 100, $slides[0]['overlay_opacity'] );
		$this->assertSame( '', $slides[0]['link_url'] );
		$this->assertSame( '_self', $slides[0]['link_target'] );
		$this->assertSame( 'full_slide', $slides[0]['cta_mode'] );
		// absint() turns -5 into 5 before the 0-100 clamp.
		$this->assertSame( 5, $slides[1]['overlay_opacity'] );
	}

	public function test_a_missing_opacity_defaults_to_fifty(): void {
		$this->save( [ 'lw_slider_slides' => [ [ 'headline' => 'x' ] ] ] );

		$this->assertSame( 50, $this->meta[ self::POST_ID ]['_lw_slider_slides'][0]['overlay_opacity'] );
	}

	public function test_settings_are_clamped_and_unchecked_boxes_turn_off(): void {
		$this->save(
			[
				'lw_slider_settings' => [
					'min_height_desktop' => '5000',
					'min_height_mobile'  => '12',
					'dots'               => '1',
					'autoplay'           => '1',
					'autoplay_delay'     => '99',
					'transition'         => 'zoom',
					'content_align_h'    => 'right',
					'content_align_v'    => 'middle',
					'custom_class'       => 'my class<script>',
				],
			]
		);

		$this->assertSame(
			[
				'min_height_desktop' => '1200',
				'min_height_mobile'  => '100',
				'dots'               => true,
				'arrows'             => false,
				'arrows_mobile'      => false,
				'autoplay'           => true,
				'autoplay_delay'     => 1000,
				'transition'         => 'slide',
				'loop'               => false,
				'content_align_h'    => 'right',
				'content_align_v'    => 'center',
				'use_default_styles' => false,
				'custom_class'       => 'myclassscript',
				'swipe'              => false,
				'keyboard'           => false,
				'pause_on_hover'     => false,
				'hide_on_mobile'     => false,
			],
			$this->meta[ self::POST_ID ]['_lw_slider_settings']
		);
	}

	public function test_backslashes_in_text_survive(): void {
		$this->save( [ 'lw_slider_slides' => [ [ 'headline' => 'C:\\path' ] ] ] );

		$this->assertSame( 'C:\\path', $this->meta[ self::POST_ID ]['_lw_slider_slides'][0]['headline'] );
	}
}
