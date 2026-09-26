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
 *
 * Every save resends every slide, so a text or link that is already stored
 * (on any slide, as slides can move) is accepted unchanged even when it
 * breaks a rule: 1.0.x had no length limits, and one old value must not
 * block saving the whole slider. Only new or edited values are checked.
 */
final class SlideValidator {

	/**
	 * Most slides a slider may hold.
	 */
	public const MAX_SLIDES = 100;

	/**
	 * Fields whose stored value is accepted unchanged (free text and links).
	 */
	private const KEEP_STORED = [ 'title', 'headline', 'subheadline', 'description', 'link_url', 'button_text', 'image_alt' ];

	/**
	 * Validate the list.
	 *
	 * @param mixed                             $slides Submitted slides.
	 * @param array<string, array<int, string>> $errors Field errors, appended to.
	 * @param array<int, array<string, mixed>>  $stored Slides stored now (on update).
	 * @return array<int, array<string, mixed>> Accepted slides (defaults filled in).
	 */
	public static function validate( $slides, array &$errors, array $stored = [] ): array {
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

			$clean[] = self::slide( $slide, 'slides.' . $index . '.', $errors, $stored );
		}

		return $clean;
	}

	/**
	 * Validate one slide.
	 *
	 * @param array<array-key, mixed>           $slide  Submitted slide.
	 * @param string                            $prefix Error key prefix.
	 * @param array<string, array<int, string>> $errors Field errors, appended to.
	 * @param array<int, array<string, mixed>>  $stored Slides stored now.
	 * @return array<string, mixed>
	 */
	private static function slide( array $slide, string $prefix, array &$errors, array $stored ): array {
		$clean = Defaults::slide();

		foreach ( $slide as $key => $value ) {
			$key   = (string) $key;
			$error = array_key_exists( $key, $clean )
				? self::field( $key, $value, $clean[ $key ] )
				: __( 'Unknown field.', 'lw-slider' );

			if ( null !== $error && self::is_stored( $key, $value, $stored ) ) {
				$clean[ $key ] = $value;
				$error         = null;
			}

			if ( null !== $error ) {
				$errors[ $prefix . $key ][] = $error;
			}
		}

		return $clean;
	}

	/**
	 * Whether a text or link value is already stored on one of the slides.
	 *
	 * @param string                           $key    Field.
	 * @param mixed                            $value  Submitted value.
	 * @param array<int, array<string, mixed>> $stored Slides stored now.
	 * @return bool
	 */
	private static function is_stored( string $key, $value, array $stored ): bool {
		if ( ! is_string( $value ) || '' === $value || ! in_array( $key, self::KEEP_STORED, true ) ) {
			return false;
		}

		return in_array( $value, array_column( $stored, $key ), true );
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
