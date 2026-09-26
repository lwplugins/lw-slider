<?php
/**
 * Individual slide markup renderer.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Frontend;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\Data\SliderSanitizer;

/**
 * Renders individual slide HTML elements.
 */
final class SlideMarkup {

	/**
	 * Render a single slide.
	 *
	 * @param array<string, mixed> $slide    Slide data.
	 * @param array<string, mixed> $settings Slider settings.
	 * @param int                  $index    Position among the shown slides.
	 * @return void
	 */
	public static function render( array $slide, array $settings, int $index = 0 ): void {
		$style    = self::build_style( $slide );
		$has_link = ! empty( $slide['link_url'] );
		$is_full  = 'full_slide' === $slide['cta_mode'];
		$target   = '_blank' === $slide['link_target'] ? $slide['link_target'] : '_self';

		echo '<li class="splide__slide"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>';

		self::render_image( $slide, $index );
		self::render_overlay( $slide );

		if ( $has_link && $is_full ) {
			printf(
				'<a href="%s" target="%s"%s class="lw-slider__link"%s>',
				esc_url( $slide['link_url'] ),
				esc_attr( $target ),
				'_blank' === $target ? ' rel="noopener"' : '',
				self::link_label( $slide ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in link_label().
			);
		}

		self::render_content( $slide, $settings, $has_link );

		if ( $has_link && $is_full ) {
			echo '</a>';
		}

		echo '</li>';
	}

	/**
	 * Build inline style for a slide.
	 *
	 * @param array<string, mixed> $slide Slide data.
	 * @return string
	 */
	private static function build_style( array $slide ): string {
		// Style values are validated again here: stored meta may predate the
		// sanitizer or come from elsewhere, and esc_attr() does not stop CSS.
		if ( 'color' === $slide['bg_type'] ) {
			return 'background-color:' . SliderSanitizer::hex( $slide['bg_color'], (string) Defaults::slide()['bg_color'] ) . ';';
		}

		return '';
	}

	/**
	 * The background image as an <img>: srcset/sizes from WordPress, the
	 * slide's alt text (else the media library's), lazy loading for every
	 * slide but the first (which is eager). object-position keeps the focus point.
	 *
	 * @param array<string, mixed> $slide Slide data.
	 * @param int                  $index Position among the shown slides.
	 * @return void
	 */
	private static function render_image( array $slide, int $index ): void {
		$id = (int) $slide['bg_image_id'];

		if ( 'image' !== $slide['bg_type'] || $id <= 0 ) {
			return;
		}

		$position = SliderSanitizer::choice( $slide['bg_position'], array_keys( Defaults::bg_positions() ), 'center center' );
		$attrs    = [
			'class'    => 'lw-slider__image',
			'sizes'    => '100vw',
			'decoding' => 'async',
			'style'    => 'object-position:' . $position . ';',
			// An explicit "eager": without a loading attribute, core's content
			// filters may still add loading="lazy" to the first slide (outside
			// the main loop, in block widgets). fetchpriority is left to core:
			// only it knows whether the image is near the top of the page.
			'loading'  => 0 === $index ? 'eager' : 'lazy',
		];

		if ( '' !== trim( (string) $slide['image_alt'] ) ) {
			$attrs['alt'] = (string) $slide['image_alt'];
		}

		echo wp_get_attachment_image( $id, 'full', false, $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core builds and escapes the tag.
	}

	/**
	 * An aria-label for a full-slide link that has no text of its own.
	 *
	 * @param array<string, mixed> $slide Slide data.
	 * @return string Attribute (with a leading space), or ''.
	 */
	private static function link_label( array $slide ): string {
		foreach ( [ 'headline', 'subheadline', 'description' ] as $key ) {
			if ( '' !== trim( (string) $slide[ $key ] ) ) {
				return '';
			}
		}

		$alt   = trim( (string) $slide['image_alt'] );
		$label = '' !== $alt ? $alt : __( 'Open the slide link', 'lw-slider' );

		return ' aria-label="' . esc_attr( $label ) . '"';
	}

	/**
	 * Render the overlay div.
	 *
	 * @param array<string, mixed> $slide Slide data.
	 * @return void
	 */
	private static function render_overlay( array $slide ): void {
		$color = SliderSanitizer::hex( $slide['overlay_color'], '' );

		if ( '' === $color ) {
			return;
		}

		$opacity = SliderSanitizer::clamp( $slide['overlay_opacity'], 0, 100 ) / 100;

		printf(
			'<div class="lw-slider__overlay" style="background-color:%s;opacity:%s;"></div>',
			esc_attr( $color ),
			esc_attr( (string) $opacity )
		);
	}

	/**
	 * Render slide content.
	 *
	 * @param array<string, mixed> $slide    Slide data.
	 * @param array<string, mixed> $settings Slider settings.
	 * @param bool                 $has_link Whether the slide has a link.
	 * @return void
	 */
	private static function render_content( array $slide, array $settings, bool $has_link ): void {
		$h_class = 'lw-align-' . SliderSanitizer::choice( $settings['content_align_h'], SliderSanitizer::ALIGNS_H, 'center' );
		$v_class = 'lw-valign-' . SliderSanitizer::choice( $settings['content_align_v'], SliderSanitizer::ALIGNS_V, 'center' );

		echo '<div class="lw-slider__content ' . esc_attr( $h_class . ' ' . $v_class ) . '">';

		if ( ! empty( $slide['headline'] ) ) {
			echo '<h2 class="lw-slider__headline">' . esc_html( $slide['headline'] ) . '</h2>';
		}

		if ( ! empty( $slide['subheadline'] ) ) {
			echo '<p class="lw-slider__subheadline">' . esc_html( $slide['subheadline'] ) . '</p>';
		}

		if ( ! empty( $slide['description'] ) ) {
			echo '<p class="lw-slider__description">' . esc_html( $slide['description'] ) . '</p>';
		}

		if ( $has_link && 'button' === $slide['cta_mode'] && ! empty( $slide['button_text'] ) ) {
			self::render_button( $slide );
		}

		echo '</div>';
	}

	/**
	 * Render CTA button.
	 *
	 * @param array<string, mixed> $slide Slide data.
	 * @return void
	 */
	private static function render_button( array $slide ): void {
		$target = '_blank' === $slide['link_target'] ? '_blank' : '_self';

		printf(
			'<a href="%s" target="%s"%s class="lw-slider__button">%s</a>',
			esc_url( $slide['link_url'] ),
			esc_attr( $target ),
			'_blank' === $target ? ' rel="noopener"' : '',
			esc_html( $slide['button_text'] )
		);
	}
}
