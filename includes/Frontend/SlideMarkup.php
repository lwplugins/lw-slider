<?php
/**
 * Individual slide markup renderer.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Frontend;

/**
 * Renders individual slide HTML elements.
 */
final class SlideMarkup {

	/**
	 * Render a single slide.
	 *
	 * @param array<string, mixed> $slide    Slide data.
	 * @param array<string, mixed> $settings Slider settings.
	 * @return void
	 */
	public static function render( array $slide, array $settings ): void {
		$style    = self::build_style( $slide );
		$has_link = ! empty( $slide['link_url'] );
		$is_full  = 'full_slide' === $slide['cta_mode'];
		$target   = '_blank' === $slide['link_target'] ? $slide['link_target'] : '_self';
		$rel      = '_blank' === $target ? ' rel="noopener"' : '';

		echo '<li class="splide__slide" style="' . esc_attr( $style ) . '">';

		self::render_overlay( $slide );

		if ( $has_link && $is_full ) {
			printf(
				'<a href="%s" target="%s"%s class="lw-slider__link">',
				esc_url( $slide['link_url'] ),
				esc_attr( $target ),
				esc_attr( $rel )
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
		if ( 'color' === $slide['bg_type'] ) {
			return 'background-color:' . esc_attr( (string) $slide['bg_color'] ) . ';';
		}

		if ( empty( $slide['bg_image_id'] ) ) {
			return '';
		}

		$url = wp_get_attachment_image_url( (int) $slide['bg_image_id'], 'full' );

		if ( ! $url ) {
			return '';
		}

		return sprintf(
			'background-image:url(%s);background-size:cover;background-position:%s;',
			esc_url( $url ),
			esc_attr( (string) $slide['bg_position'] )
		);
	}

	/**
	 * Render the overlay div.
	 *
	 * @param array<string, mixed> $slide Slide data.
	 * @return void
	 */
	private static function render_overlay( array $slide ): void {
		if ( empty( $slide['overlay_color'] ) ) {
			return;
		}

		$opacity = (int) $slide['overlay_opacity'] / 100;

		printf(
			'<div class="lw-slider__overlay" style="background-color:%s;opacity:%s;"></div>',
			esc_attr( (string) $slide['overlay_color'] ),
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
		$h_class = 'lw-align-' . esc_attr( (string) $settings['content_align_h'] );
		$v_class = 'lw-valign-' . esc_attr( (string) $settings['content_align_v'] );

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
		$rel    = '_blank' === $target ? ' rel="noopener"' : '';

		printf(
			'<a href="%s" target="%s"%s class="lw-slider__button">%s</a>',
			esc_url( $slide['link_url'] ),
			esc_attr( $target ),
			esc_attr( $rel ),
			esc_html( $slide['button_text'] )
		);
	}
}
