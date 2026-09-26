<?php
/**
 * Field rules of the admin REST input.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Rest\Input;

/**
 * Strict checks for one submitted value. Each returns a translated error
 * message, or null and writes the accepted value to $clean. Unlike the
 * sanitizer (which silently coerces), these reject what they cannot accept,
 * so the admin can show the message next to the field.
 */
final class Rules {

	/**
	 * A boolean (JSON true/false only).
	 *
	 * @param mixed $value Submitted value.
	 * @param mixed $clean Accepted value.
	 * @return string|null
	 */
	public static function bool( $value, &$clean ): ?string {
		if ( ! is_bool( $value ) ) {
			return __( 'Must be on or off.', 'lw-slider' );
		}

		$clean = $value;
		return null;
	}

	/**
	 * A whole number within a range (a digit-only string is accepted).
	 *
	 * @param mixed $value Submitted value.
	 * @param int   $min   Minimum.
	 * @param int   $max   Maximum.
	 * @param mixed $clean Accepted value.
	 * @return string|null
	 */
	public static function int( $value, int $min, int $max, &$clean ): ?string {
		$number = null;

		if ( is_int( $value ) ) {
			$number = $value;
		} elseif ( is_string( $value ) && 1 === preg_match( '/^\s*-?\d{1,9}\s*$/', $value ) ) {
			$number = (int) trim( $value );
		}

		if ( null === $number || $number < $min || $number > $max ) {
			return sprintf(
				/* translators: 1: smallest allowed number, 2: largest allowed number. */
				__( 'Enter a whole number from %1$d to %2$d.', 'lw-slider' ),
				$min,
				$max
			);
		}

		$clean = $number;
		return null;
	}

	/**
	 * One of a fixed list of strings.
	 *
	 * @param mixed         $value   Submitted value.
	 * @param array<string> $allowed Allowed values.
	 * @param mixed         $clean   Accepted value.
	 * @return string|null
	 */
	public static function choice( $value, array $allowed, &$clean ): ?string {
		if ( ! is_string( $value ) || ! in_array( $value, $allowed, true ) ) {
			return __( 'Choose one of the listed options.', 'lw-slider' );
		}

		$clean = $value;
		return null;
	}

	/**
	 * A string of at most $max characters.
	 *
	 * @param mixed $value Submitted value.
	 * @param int   $max   Longest allowed length.
	 * @param mixed $clean Accepted value.
	 * @return string|null
	 */
	public static function text( $value, int $max, &$clean ): ?string {
		if ( ! is_string( $value ) ) {
			return __( 'Must be text.', 'lw-slider' );
		}

		if ( mb_strlen( $value ) > $max ) {
			return sprintf(
				/* translators: %d: largest allowed number of characters. */
				__( 'Too long: at most %d characters.', 'lw-slider' ),
				$max
			);
		}

		$clean = $value;
		return null;
	}

	/**
	 * A #rgb or #rrggbb color ('' too when $optional).
	 *
	 * @param mixed $value    Submitted value.
	 * @param bool  $optional Whether '' is accepted.
	 * @param mixed $clean    Accepted value.
	 * @return string|null
	 */
	public static function hex( $value, bool $optional, &$clean ): ?string {
		if ( $optional && '' === $value ) {
			$clean = '';
			return null;
		}

		if ( ! is_string( $value ) || 1 !== preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $value ) ) {
			return __( 'Enter a color as #rgb or #rrggbb.', 'lw-slider' );
		}

		$clean = $value;
		return null;
	}

	/**
	 * A link: '', a path on this site, or an address with one of the
	 * protocols WordPress allows in links (wp_allowed_protocols(): http,
	 * mailto, tel, sms, ftp, ...), as 1.0.x saved and the site prints them.
	 *
	 * @param mixed $value Submitted value.
	 * @param mixed $clean Accepted (normalized) value.
	 * @return string|null
	 */
	public static function url( $value, &$clean ): ?string {
		$error = __( 'Enter a web address (https://...) or a path that starts with /.', 'lw-slider' );

		if ( ! is_string( $value ) || mb_strlen( $value ) > 2048 ) {
			return $error;
		}

		$value = trim( $value );
		$url   = '' === $value ? '' : esc_url_raw( $value );

		if ( '' !== $value && '' === $url ) {
			return $error;
		}

		$clean = $url;
		return null;
	}

	/**
	 * One CSS class name, or ''.
	 *
	 * @param mixed $value Submitted value.
	 * @param mixed $clean Accepted value.
	 * @return string|null
	 */
	public static function css_class( $value, &$clean ): ?string {
		if ( ! is_string( $value ) || mb_strlen( $value ) > 100 || sanitize_html_class( $value ) !== $value ) {
			return __( 'Use one CSS class name: letters, digits, hyphens and underscores.', 'lw-slider' );
		}

		$clean = $value;
		return null;
	}
}
