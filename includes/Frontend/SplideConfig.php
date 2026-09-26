<?php
/**
 * Splide options of a slider.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Frontend;

/**
 * Builds the Splide options (the data-lw-slider JSON) from the settings:
 * the carousel label, translated control labels, arrow keys only while the
 * slider has focus, and autoplay pausing.
 */
final class SplideConfig {

	/**
	 * Options for one slider.
	 *
	 * @param array<string, mixed> $s           Settings (overrides applied).
	 * @param int                  $slide_count Number of active slides.
	 * @param string               $label       Accessible name of the carousel.
	 * @return array<string, mixed>
	 */
	public static function build( array $s, int $slide_count, string $label ): array {
		$config = [
			'type'       => ! empty( $s['loop'] ) ? 'loop' : 'slide',
			'pagination' => ! empty( $s['dots'] ) && $slide_count > 1,
			'arrows'     => ! empty( $s['arrows'] ) && $slide_count > 1,
			'drag'       => ! empty( $s['swipe'] ),
			// 'focused': the arrow keys act only inside this slider, never
			// in form fields elsewhere on the page or on every slider at once.
			'keyboard'   => ! empty( $s['keyboard'] ) ? 'focused' : false,
			'label'      => $label,
			'i18n'       => self::i18n(),
		];

		if ( 'fade' === $s['transition'] ) {
			$config['type']   = 'fade';
			$config['rewind'] = true;
		}

		if ( self::has_autoplay( $s, $slide_count ) ) {
			$config['autoplay']     = true;
			$config['interval']     = (int) $s['autoplay_delay'];
			$config['pauseOnHover'] = ! empty( $s['pause_on_hover'] );
			// Always: keyboard and screen reader users must be able to stop
			// the motion while they are inside the slider (WCAG 2.2.2).
			$config['pauseOnFocus'] = true;
		}

		return $config;
	}

	/**
	 * Whether the slider plays on its own (and so gets a pause button).
	 *
	 * @param array<string, mixed> $s           Settings.
	 * @param int                  $slide_count Number of active slides.
	 * @return bool
	 */
	public static function has_autoplay( array $s, int $slide_count ): bool {
		return ! empty( $s['autoplay'] ) && $slide_count > 1;
	}

	/**
	 * Splide's control labels, translated.
	 *
	 * @return array<string, string>
	 */
	public static function i18n(): array {
		return [
			'prev'       => __( 'Previous slide', 'lw-slider' ),
			'next'       => __( 'Next slide', 'lw-slider' ),
			'first'      => __( 'Go to first slide', 'lw-slider' ),
			'last'       => __( 'Go to last slide', 'lw-slider' ),
			/* translators: %s: slide number (Splide fills it in). */
			'slideX'     => __( 'Go to slide %s', 'lw-slider' ),
			/* translators: %s: page number (Splide fills it in). */
			'pageX'      => __( 'Go to page %s', 'lw-slider' ),
			'play'       => __( 'Start autoplay', 'lw-slider' ),
			'pause'      => __( 'Pause autoplay', 'lw-slider' ),
			'carousel'   => __( 'carousel', 'lw-slider' ),
			'slide'      => __( 'slide', 'lw-slider' ),
			'select'     => __( 'Select a slide to show', 'lw-slider' ),
			'slideLabel' => self::slide_label(),
		];
	}

	/**
	 * The "slide 2 of 5" label in Splide's format. Splide replaces each
	 * plain %s in turn and knows no numbered placeholders, so %1$s and
	 * %2$s become %s (the numbers therefore keep their order).
	 *
	 * @return string
	 */
	public static function slide_label(): string {
		/* translators: 1: slide number, 2: number of slides. Keep this order: the slider fills them in from left to right. */
		$label = __( '%1$s of %2$s', 'lw-slider' );

		return (string) preg_replace( '/%\d+\$s/', '%s', $label );
	}
}
