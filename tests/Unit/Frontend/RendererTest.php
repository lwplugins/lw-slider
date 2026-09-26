<?php
/**
 * Characterization tests for the slider markup.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Frontend;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Data\SliderRepository;
use LightweightPlugins\Slider\Frontend\Renderer;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;
use LightweightPlugins\Slider\Tests\Unit\StubsWordPress;

/**
 * What the shortcode and the block print for a slider.
 */
final class RendererTest extends MonkeyTestCase {

	use StubsWordPress;

	private const ID = 12;

	protected function setUp(): void {
		parent::setUp();
		$this->stub_wordpress();
		$this->stub_meta_store();
		Functions\when( 'wp_get_attachment_image_url' )->alias( static fn( $id ) => 'https://example.test/img-' . $id . '.jpg' );
		Functions\when( 'wp_get_attachment_image' )->alias(
			static fn( $id, $size, $icon, $attrs ) => '<img data-id="' . $id . '" data-attrs=\'' . json_encode( $attrs ) . '\'>'
		);
		Functions\when( 'get_post_field' )->justReturn( 'Home <b>hero</b>' );
		Functions\when( 'wp_strip_all_tags' )->alias( static fn( $text ) => strip_tags( (string) $text ) );
	}

	/**
	 * Store slides/settings and render.
	 *
	 * @param array<int, array<string, mixed>> $slides    Slides.
	 * @param array<string, mixed>             $settings  Settings.
	 * @param array<string, mixed>             $overrides Overrides.
	 * @return string
	 */
	private function render( array $slides, array $settings = [], array $overrides = [] ): string {
		$this->meta[ self::ID ][ SliderRepository::SLIDES_KEY ] = $slides;

		unset( $this->meta[ self::ID ][ SliderRepository::SETTINGS_KEY ] );

		if ( [] !== $settings ) {
			$this->meta[ self::ID ][ SliderRepository::SETTINGS_KEY ] = $settings;
		}

		return Renderer::render( self::ID, $overrides );
	}

	public function test_nothing_without_active_slides(): void {
		$this->assertSame( '', $this->render( [] ) );
		$this->assertSame( '', $this->render( [ [ 'active' => false, 'headline' => 'x' ] ] ) );
	}

	public function test_only_active_slides_are_printed(): void {
		$html = $this->render(
			[
				[ 'active' => true, 'headline' => 'One' ],
				[ 'active' => false, 'headline' => 'Two' ],
			]
		);

		$this->assertStringContainsString( 'One', $html );
		$this->assertStringNotContainsString( 'Two', $html );
		$this->assertSame( 1, substr_count( $html, 'class="splide__slide"' ) );
	}

	public function test_root_carries_classes_and_the_splide_config(): void {
		$html = $this->render(
			[ [ 'active' => true ], [ 'active' => true ] ],
			[ 'custom_class' => 'hero', 'hide_on_mobile' => true, 'autoplay' => true, 'autoplay_delay' => 3000 ]
		);

		$this->assertMatchesRegularExpression( '/class="lw-slider lw-slider--styled lw-slider--no-arrows-mobile lw-slider--hide-mobile hero splide"/', $html );
		$this->assertStringContainsString( '&quot;autoplay&quot;:true', $html );
		$this->assertStringContainsString( '&quot;interval&quot;:3000', $html );
		$this->assertStringContainsString( '&quot;pagination&quot;:true', $html );
	}

	public function test_one_slide_has_no_dots_or_arrows(): void {
		$html = $this->render( [ [ 'active' => true ] ] );

		$this->assertStringContainsString( '&quot;pagination&quot;:false', $html );
		$this->assertStringContainsString( '&quot;arrows&quot;:false', $html );
	}

	public function test_overrides_win_over_stored_settings(): void {
		$html = $this->render( [ [ 'active' => true ], [ 'active' => true ] ], [ 'transition' => 'slide' ], [ 'transition' => 'fade' ] );

		$this->assertStringContainsString( '&quot;type&quot;:&quot;fade&quot;', $html );
		$this->assertStringContainsString( '&quot;rewind&quot;:true', $html );
	}

	public function test_a_color_slide_with_overlay_content_and_button(): void {
		$html = $this->render(
			[
				[
					'active'          => true,
					'bg_type'         => 'color',
					'bg_color'        => '#123456',
					'overlay_color'   => '#000000',
					'overlay_opacity' => 40,
					'headline'        => 'Big <news>',
					'link_url'        => 'https://example.test/go',
					'cta_mode'        => 'button',
					'button_text'     => 'Read',
				],
			]
		);

		$this->assertStringContainsString( 'background-color:#123456;', $html );
		$this->assertStringContainsString( 'background-color:#000000;opacity:0.4;', $html );
		$this->assertStringContainsString( '<h2 class="lw-slider__headline">Big &lt;news&gt;</h2>', $html );
		$this->assertStringContainsString( 'class="lw-slider__button">Read</a>', $html );
		$this->assertStringNotContainsString( 'lw-slider__link', $html );
	}

	public function test_image_slides_print_an_img_with_alt_lazy_after_the_first(): void {
		$html = $this->render(
			[
				[ 'active' => true, 'bg_image_id' => 7, 'bg_position' => 'left top', 'image_alt' => 'Beach' ],
				[ 'active' => true, 'bg_image_id' => 8, 'bg_position' => 'center;x:y' ],
			]
		);

		$this->assertStringContainsString( '<img data-id="7" data-attrs=\'{"class":"lw-slider__image","sizes":"100vw","decoding":"async","style":"object-position:left top;","loading":false,"alt":"Beach"}\'>', $html );
		$this->assertStringContainsString( '"style":"object-position:center center;","loading":"lazy"}', $html );
		$this->assertStringNotContainsString( 'background-image', $html );
	}

	public function test_carousel_label_keyboard_and_translated_controls(): void {
		$html = $this->render( [ [ 'active' => true ], [ 'active' => true ] ] );

		$this->assertStringContainsString( '&quot;label&quot;:&quot;Home hero&quot;', $html );
		$this->assertStringContainsString( '&quot;keyboard&quot;:&quot;focused&quot;', $html );
		$this->assertStringContainsString( '&quot;play&quot;:&quot;Start autoplay&quot;', $html );
	}

	public function test_autoplay_adds_a_pause_button_and_pauses_on_hover_by_default(): void {
		$html = $this->render( [ [ 'active' => true ], [ 'active' => true ] ], [ 'autoplay' => true ] );

		$this->assertStringContainsString( '<button class="splide__toggle lw-slider__toggle" type="button" aria-label="Pause autoplay">', $html );
		$this->assertStringContainsString( '&quot;pauseOnHover&quot;:true', $html );
		$this->assertStringContainsString( '&quot;pauseOnFocus&quot;:true', $html );
		$this->assertStringNotContainsString( 'splide__toggle', $this->render( [ [ 'active' => true ], [ 'active' => true ] ] ) );
		$this->assertStringNotContainsString( 'splide__toggle', $this->render( [ [ 'active' => true ] ], [ 'autoplay' => true ] ) );
	}

	public function test_a_full_slide_link_without_text_is_named_by_the_alt_text(): void {
		$html = $this->render( [ [ 'active' => true, 'link_url' => '/x', 'image_alt' => 'Sale' ], [ 'active' => true, 'link_url' => '/y' ] ] );

		$this->assertStringContainsString( 'class="lw-slider__link" aria-label="Sale">', $html );
		$this->assertStringContainsString( 'class="lw-slider__link" aria-label="Open the slide link">', $html );
	}

	public function test_a_full_slide_link_wraps_the_content(): void {
		$html = $this->render( [ [ 'active' => true, 'link_url' => 'https://example.test/', 'headline' => 'H' ] ] );

		$this->assertMatchesRegularExpression( '#<a href="https://example.test/" target="_self" class="lw-slider__link">.*H.*</a>#s', $html );
		$this->assertStringNotContainsString( 'aria-label', $html );
	}

	public function test_stored_css_in_style_values_never_reaches_the_markup(): void {
		$html = $this->render(
			[
				[
					'active'          => true,
					'bg_type'         => 'color',
					'bg_color'        => '#000;background:url(//evil)',
					'overlay_color'   => 'red;position:fixed',
					'overlay_opacity' => '50;x',
				],
				[
					'active'      => true,
					'bg_image_id' => 7,
					'bg_position' => 'center;background:url(//evil)',
				],
			],
			[
				'min_height_desktop' => '400px;position:fixed',
				'min_height_mobile'  => 'x}body{display:none',
				'content_align_h'    => 'left" onmouseover="x',
			]
		);

		$this->assertStringNotContainsString( 'evil', $html );
		$this->assertStringNotContainsString( 'position:fixed', $html );
		$this->assertStringNotContainsString( 'display:none', $html );
		$this->assertStringNotContainsString( 'onmouseover', $html );
		$this->assertStringContainsString( 'background-color:#f0f0f0;', $html );
		$this->assertStringNotContainsString( 'lw-slider__overlay', $html );
		$this->assertStringContainsString( '--lw-slider-min-height:400px;', $html );
		$this->assertStringContainsString( '--lw-slider-min-height-mobile:100px;', $html );
		$this->assertStringContainsString( 'lw-align-center', $html );
	}

	public function test_min_height_is_clamped(): void {
		$html = $this->render( [ [ 'active' => true ] ], [ 'min_height_desktop' => '9999', 'min_height_mobile' => '3' ] );

		$this->assertStringContainsString( '--lw-slider-min-height:1200px;', $html );
		$this->assertStringContainsString( '--lw-slider-min-height-mobile:100px;', $html );
	}

	public function test_no_inline_min_height_or_style_block_overrides_the_mobile_height(): void {
		$html = $this->render( [ [ 'active' => true ] ] );

		$this->assertDoesNotMatchRegularExpression( '/[;"]min-height:/', $html );
		$this->assertStringNotContainsString( '<style', $html );
	}

	public function test_a_slider_shown_twice_gets_unique_ids(): void {
		$this->meta[ 99 ][ SliderRepository::SLIDES_KEY ] = [ [ 'active' => true ] ];

		$first  = Renderer::render( 99 );
		$second = Renderer::render( 99 );

		$this->assertStringContainsString( 'id="lw-slider-99"', $first );
		$this->assertMatchesRegularExpression( '/id="lw-slider-99-\d+"/', $second );
	}

	public function test_new_tab_links_carry_a_valid_rel_attribute(): void {
		$html = $this->render(
			[
				[ 'active' => true, 'link_url' => 'https://example.test/a', 'link_target' => '_blank' ],
				[ 'active' => true, 'link_url' => 'https://example.test/b', 'link_target' => '_blank', 'cta_mode' => 'button', 'button_text' => 'Go' ],
			]
		);

		$this->assertSame( 2, substr_count( $html, 'target="_blank" rel="noopener"' ) );
		$this->assertStringNotContainsString( '&quot;noopener', $html );
	}
}
