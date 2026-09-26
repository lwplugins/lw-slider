<?php
/**
 * Validates submitted slides.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Rest\Input;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\Data\SliderSanitizer;

/**
 * Checks a full, ordered slide list (the list replaces the stored one).
 * A slide may leave keys out (they get their default); an unknown key or an
 * invalid value is an error keyed `slides.{index}.{field}`.
 */
final class SlideValidator {

	/**
	 * Most slides a slider may hold.
	 */
	public const MAX_SLIDES = 100;

	/**
	 * Validate the list.
	 *
	 * @param mixed                             $slides Submitted slides.
	 * @param array<string, array<int, string>> $errors Field errors, appended to.
	 * @return array<int, array<string, mixed>> Accepted slides (defaults filled in).
	 */
	public static function validate( $slides, array &$errors ): array {
		if ( ! is_array( $slides ) || array_values( $slides ) !== $slides ) {
			$errors['slides'][] = __( 'Must be a list of slides.', 'lw-slider' );
			return [];
		}

		if ( count( $slides ) > self::MAX_SLIDES ) {
			/* translators: %d: largest allowed number of slides. */
			$errors['slides'][] = sprintf( __( 'A slider can hold at most %d slides.', 'lw-slider' ), self::MAX_SLIDES );
			return [];
		}

		$clean = [];

		foreach ( $slides as $index => $slide ) {
			if ( ! is_array( $slide ) ) {
				$errors[ 'slides.' . $index ][] = __( 'Each slide must be an object.', 'lw-slider' );
				continue;
			}

			$clean[] = self::slide( $slide, 'slides.' . $index . '.', $errors );
		}

		return $clean;
	}

	/**
	 * Validate one slide.
	 *
	 * @param array<array-key, mixed>           $slide  Submitted slide.
	 * @param string                            $prefix Error key prefix.
	 * @param array<string, array<int, string>> $errors Field errors, appended to.
	 * @return array<string, mixed>
	 */
	private static function slide( array $slide, string $prefix, array &$errors ): array {
		$clean = Defaults::slide();

		foreach ( $slide as $key => $value ) {
			$key   = (string) $key;
			$error = array_key_exists( $key, $clean )
				? self::field( $key, $value, $clean[ $key ] )
				: __( 'Unknown field.', 'lw-slider' );

			if ( null !== $error ) {
				$errors[ $prefix . $key ][] = $error;
			}
		}

		return $clean;
	}

	/**
	 * Check one field by its rule.
	 *
	 * @param string $key   Field.
	 * @param mixed  $value Submitted value.
	 * @param mixed  $clean Accepted value.
	 * @return string|null
	 */
	private static function field( string $key, $value, &$clean ): ?string {
		switch ( $key ) {
			case 'active':
				return Rules::bool( $value, $clean );
			case 'bg_type':
				return Rules::choice( $value, SliderSanitizer::BG_TYPES, $clean );
			case 'bg_image_id':
				return Rules::int( $value, 0, PHP_INT_MAX, $clean );
			case 'bg_color':
				return Rules::hex( $value, false, $clean );
			case 'bg_position':
				return Rules::choice( $value, array_keys( Defaults::bg_positions() ), $clean );
			case 'overlay_color':
				return Rules::hex( $value, true, $clean );
			case 'overlay_opacity':
				return Rules::int( $value, 0, 100, $clean );
			case 'description':
				return Rules::text( $value, 5000, $clean );
			case 'link_url':
				return Rules::url( $value, $clean );
			case 'link_target':
				return Rules::choice( $value, SliderSanitizer::LINK_TARGETS, $clean );
			case 'cta_mode':
				return Rules::choice( $value, SliderSanitizer::CTA_MODES, $clean );
			default:
				// title, headline, subheadline, button_text, image_alt.
				return Rules::text( $value, 500, $clean );
		}
	}
}
