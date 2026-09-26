<?php
/**
 * Validates submitted slider settings.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Rest\Input;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\Data\SliderSanitizer;

/**
 * Checks a partial settings object: only the submitted keys change (a
 * switch that is not sent is never turned off). Errors are keyed
 * `settings.{key}`.
 */
final class SettingsValidator {

	/**
	 * Keys that are on/off switches.
	 */
	private const BOOLEANS = [ 'dots', 'arrows', 'arrows_mobile', 'autoplay', 'loop', 'use_default_styles', 'swipe', 'keyboard', 'pause_on_hover', 'hide_on_mobile' ];

	/**
	 * Validate a partial settings object.
	 *
	 * @param mixed                             $settings Submitted settings.
	 * @param array<string, array<int, string>> $errors   Field errors, appended to.
	 * @return array<string, mixed> Accepted changes.
	 */
	public static function validate( $settings, array &$errors ): array {
		if ( ! is_array( $settings ) || ( [] !== $settings && array_values( $settings ) === $settings ) ) {
			$errors['settings'][] = __( 'Must be an object of settings.', 'lw-slider' );
			return [];
		}

		$known = Defaults::settings();
		$clean = [];

		foreach ( $settings as $key => $value ) {
			$key   = (string) $key;
			$error = array_key_exists( $key, $known )
				? self::field( $key, $value, $clean[ $key ] )
				: __( 'Unknown setting.', 'lw-slider' );

			if ( null !== $error ) {
				unset( $clean[ $key ] );
				$errors[ 'settings.' . $key ][] = $error;
			}
		}

		return $clean;
	}

	/**
	 * Check one setting by its rule.
	 *
	 * @param string $key   Setting.
	 * @param mixed  $value Submitted value.
	 * @param mixed  $clean Accepted value.
	 * @return string|null
	 */
	private static function field( string $key, $value, &$clean ): ?string {
		if ( in_array( $key, self::BOOLEANS, true ) ) {
			return Rules::bool( $value, $clean );
		}

		switch ( $key ) {
			case 'min_height_desktop':
			case 'min_height_mobile':
				return Rules::int( $value, SliderSanitizer::MIN_HEIGHT[0], SliderSanitizer::MIN_HEIGHT[1], $clean );
			case 'autoplay_delay':
				return Rules::int( $value, SliderSanitizer::AUTOPLAY_DELAY[0], SliderSanitizer::AUTOPLAY_DELAY[1], $clean );
			case 'transition':
				return Rules::choice( $value, array_keys( Defaults::transitions() ), $clean );
			case 'content_align_h':
				return Rules::choice( $value, SliderSanitizer::ALIGNS_H, $clean );
			case 'content_align_v':
				return Rules::choice( $value, SliderSanitizer::ALIGNS_V, $clean );
			default:
				// custom_class.
				return Rules::css_class( $value, $clean );
		}
	}
}
