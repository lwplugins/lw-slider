<?php
/**
 * Slide and slider settings sanitizer.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Data;

/**
 * Turns raw (unslashed) slide and settings arrays into the stored meta shape.
 *
 * The one place the meta rules live: the classic form, the admin REST API
 * and the registered meta sanitize callbacks all go through it. Unknown keys
 * are dropped, missing keys get their default (booleans: false, like an
 * unchecked box) and every value is coerced into its allowed range or list.
 */
final class SliderSanitizer {

	/**
	 * Allowed horizontal content alignments.
	 */
	public const ALIGNS_H = [ 'left', 'center', 'right' ];

	/**
	 * Allowed vertical content alignments.
	 */
	public const ALIGNS_V = [ 'top', 'center', 'bottom' ];

	/**
	 * Allowed background types.
	 */
	public const BG_TYPES = [ 'image', 'color' ];

	/**
	 * Allowed call-to-action modes.
	 */
	public const CTA_MODES = [ 'full_slide', 'button' ];

	/**
	 * Allowed link targets.
	 */
	public const LINK_TARGETS = [ '_self', '_blank' ];

	/**
	 * Min height range in pixels.
	 */
	public const MIN_HEIGHT = [ 100, 1200 ];

	/**
	 * Autoplay delay range in milliseconds.
	 */
	public const AUTOPLAY_DELAY = [ 1000, 30000 ];

	/**
	 * Sanitize a list of slides. Items that are not arrays are dropped.
	 *
	 * @param mixed $raw Raw slides.
	 * @return array<int, array<string, mixed>>
	 */
	public static function slides( $raw ): array {
		if ( ! is_array( $raw ) ) {
			return [];
		}

		$slides = [];

		foreach ( $raw as $slide ) {
			if ( is_array( $slide ) ) {
				$slides[] = self::slide( $slide );
			}
		}

		return $slides;
	}

	/**
	 * Sanitize one slide.
	 *
	 * @param array<string, mixed> $raw Raw slide data.
	 * @return array<string, mixed>
	 */
	public static function slide( array $raw ): array {
		$defaults = Defaults::slide();

		return [
			'title'           => sanitize_text_field( $raw['title'] ?? $defaults['title'] ),
			'active'          => ! empty( $raw['active'] ),
			'bg_type'         => self::choice( $raw['bg_type'] ?? '', self::BG_TYPES, $defaults['bg_type'] ),
			'bg_image_id'     => absint( $raw['bg_image_id'] ?? 0 ),
			'bg_color'        => self::hex( $raw['bg_color'] ?? '', $defaults['bg_color'] ),
			'bg_position'     => self::choice( $raw['bg_position'] ?? '', array_keys( Defaults::bg_positions() ), $defaults['bg_position'] ),
			'overlay_color'   => self::hex( $raw['overlay_color'] ?? '', '' ),
			'overlay_opacity' => self::clamp( $raw['overlay_opacity'] ?? 50, 0, 100 ),
			'headline'        => sanitize_text_field( $raw['headline'] ?? '' ),
			'subheadline'     => sanitize_text_field( $raw['subheadline'] ?? '' ),
			'description'     => sanitize_textarea_field( $raw['description'] ?? '' ),
			'link_url'        => esc_url_raw( is_string( $raw['link_url'] ?? '' ) ? ( $raw['link_url'] ?? '' ) : '' ),
			'link_target'     => self::choice( $raw['link_target'] ?? '', self::LINK_TARGETS, $defaults['link_target'] ),
			'cta_mode'        => self::choice( $raw['cta_mode'] ?? '', self::CTA_MODES, $defaults['cta_mode'] ),
			'button_text'     => sanitize_text_field( $raw['button_text'] ?? '' ),
			'image_alt'       => sanitize_text_field( $raw['image_alt'] ?? '' ),
		];
	}

	/**
	 * Sanitize the slider settings.
	 *
	 * @param mixed $raw Raw settings.
	 * @return array<string, mixed>
	 */
	public static function settings( $raw ): array {
		$raw      = is_array( $raw ) ? $raw : [];
		$defaults = Defaults::settings();

		return [
			'min_height_desktop' => (string) self::clamp( $raw['min_height_desktop'] ?? 400, ...self::MIN_HEIGHT ),
			'min_height_mobile'  => (string) self::clamp( $raw['min_height_mobile'] ?? 280, ...self::MIN_HEIGHT ),
			'dots'               => ! empty( $raw['dots'] ),
			'arrows'             => ! empty( $raw['arrows'] ),
			'arrows_mobile'      => ! empty( $raw['arrows_mobile'] ),
			'autoplay'           => ! empty( $raw['autoplay'] ),
			'autoplay_delay'     => self::clamp( $raw['autoplay_delay'] ?? 5000, ...self::AUTOPLAY_DELAY ),
			'transition'         => self::choice( $raw['transition'] ?? '', array_keys( Defaults::transitions() ), $defaults['transition'] ),
			'loop'               => ! empty( $raw['loop'] ),
			'content_align_h'    => self::choice( $raw['content_align_h'] ?? '', self::ALIGNS_H, $defaults['content_align_h'] ),
			'content_align_v'    => self::choice( $raw['content_align_v'] ?? '', self::ALIGNS_V, $defaults['content_align_v'] ),
			'use_default_styles' => ! empty( $raw['use_default_styles'] ),
			'custom_class'       => sanitize_html_class( is_string( $raw['custom_class'] ?? '' ) ? ( $raw['custom_class'] ?? '' ) : '' ),
			'swipe'              => ! empty( $raw['swipe'] ),
			'keyboard'           => ! empty( $raw['keyboard'] ),
			'pause_on_hover'     => ! empty( $raw['pause_on_hover'] ),
			'hide_on_mobile'     => ! empty( $raw['hide_on_mobile'] ),
		];
	}

	/**
	 * A value from a fixed list, or the fallback.
	 *
	 * @param mixed         $value    Raw value.
	 * @param array<string> $allowed  Allowed values.
	 * @param string        $fallback Fallback.
	 * @return string
	 */
	public static function choice( $value, array $allowed, string $fallback ): string {
		return is_string( $value ) && in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	/**
	 * A #rgb / #rrggbb color, or the fallback.
	 *
	 * @param mixed  $value    Raw value.
	 * @param string $fallback Fallback.
	 * @return string
	 */
	public static function hex( $value, string $fallback ): string {
		$color = is_string( $value ) ? sanitize_hex_color( $value ) : null;

		return is_string( $color ) && '' !== $color ? $color : $fallback;
	}

	/**
	 * A non-negative whole number clamped into a range.
	 *
	 * @param mixed $value Raw value.
	 * @param int   $min   Minimum.
	 * @param int   $max   Maximum.
	 * @return int
	 */
	public static function clamp( $value, int $min, int $max ): int {
		$number = is_scalar( $value ) ? absint( $value ) : $min;

		return min( $max, max( $min, $number ) );
	}
}
