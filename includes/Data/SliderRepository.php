<?php
/**
 * Slider meta storage.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Data;

/**
 * Reads and writes the two meta keys a slider is made of. The stored shape
 * is unchanged since 1.0: a list of slide arrays and a settings array.
 */
final class SliderRepository {

	/**
	 * Slides meta key.
	 */
	public const SLIDES_KEY = '_lw_slider_slides';

	/**
	 * Settings meta key.
	 */
	public const SETTINGS_KEY = '_lw_slider_settings';

	/**
	 * Stored slides as they are (no defaults), or an empty list.
	 *
	 * @param int $post_id Slider ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function raw_slides( int $post_id ): array {
		$slides = get_post_meta( $post_id, self::SLIDES_KEY, true );

		return is_array( $slides ) ? array_values( array_filter( $slides, 'is_array' ) ) : [];
	}

	/**
	 * Stored slides with every missing key filled from the defaults.
	 *
	 * @param int $post_id Slider ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function slides( int $post_id ): array {
		return array_map(
			static fn( array $slide ): array => wp_parse_args( $slide, Defaults::slide() ),
			self::raw_slides( $post_id )
		);
	}

	/**
	 * Stored settings over the defaults.
	 *
	 * @param int $post_id Slider ID.
	 * @return array<string, mixed>
	 */
	public static function settings( int $post_id ): array {
		$settings = get_post_meta( $post_id, self::SETTINGS_KEY, true );

		return wp_parse_args( is_array( $settings ) ? $settings : [], Defaults::settings() );
	}

	/**
	 * Store already sanitized slides.
	 *
	 * @param int                              $post_id Slider ID.
	 * @param array<int, array<string, mixed>> $slides  Sanitized slides.
	 * @return void
	 */
	public static function save_slides( int $post_id, array $slides ): void {
		// update_post_meta() unslashes its value; slash it so backslashes survive.
		update_post_meta( $post_id, self::SLIDES_KEY, wp_slash( array_values( $slides ) ) );
	}

	/**
	 * Store already sanitized settings.
	 *
	 * @param int                  $post_id  Slider ID.
	 * @param array<string, mixed> $settings Sanitized settings.
	 * @return void
	 */
	public static function save_settings( int $post_id, array $settings ): void {
		update_post_meta( $post_id, self::SETTINGS_KEY, wp_slash( $settings ) );
	}
}
