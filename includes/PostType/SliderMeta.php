<?php
/**
 * Slider meta registration.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\PostType;

use LightweightPlugins\Slider\Data\SliderRepository;
use LightweightPlugins\Slider\Data\SliderSanitizer;

/**
 * Registers the two slider meta keys. Every write, from any code path
 * (update_post_meta, XML-RPC, other plugins), runs through the
 * shared sanitizer, and only users who may edit the slider may change them.
 * They are not in the core REST API (neither is the post type): the admin
 * and the block use the plugin's own lw-slider/v1 routes.
 *
 * No `default` is registered on purpose: get_post_meta() keeps returning
 * '' for a slider without meta, as it always has.
 */
final class SliderMeta {

	/**
	 * Register both keys (on init).
	 *
	 * @return void
	 */
	public static function register(): void {
		register_post_meta(
			SliderPostType::POST_TYPE,
			SliderRepository::SLIDES_KEY,
			[
				'type'              => 'array',
				'single'            => true,
				'description'       => __( 'Slides of the slider, in order.', 'lw-slider' ),
				'sanitize_callback' => [ self::class, 'sanitize_slides' ],
				'auth_callback'     => [ self::class, 'can_edit' ],
				'show_in_rest'      => false,
			]
		);

		register_post_meta(
			SliderPostType::POST_TYPE,
			SliderRepository::SETTINGS_KEY,
			[
				'type'              => 'object',
				'single'            => true,
				'description'       => __( 'Slider settings.', 'lw-slider' ),
				'sanitize_callback' => [ self::class, 'sanitize_settings' ],
				'auth_callback'     => [ self::class, 'can_edit' ],
				'show_in_rest'      => false,
			]
		);
	}

	/**
	 * Sanitize callback of the slides key.
	 *
	 * @param mixed $value Unslashed meta value.
	 * @return array<int, array<string, mixed>>
	 */
	public static function sanitize_slides( $value ): array {
		return SliderSanitizer::slides( $value );
	}

	/**
	 * Sanitize callback of the settings key.
	 *
	 * @param mixed $value Unslashed meta value.
	 * @return array<string, mixed>
	 */
	public static function sanitize_settings( $value ): array {
		return SliderSanitizer::settings( $value );
	}

	/**
	 * Auth callback: the user may edit this slider.
	 *
	 * @param bool   $allowed  Current decision (ignored).
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Slider ID.
	 * @param int    $user_id  User ID.
	 * @return bool
	 */
	public static function can_edit( $allowed, $meta_key, $post_id, $user_id = 0 ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed -- Core's auth_callback signature.
		return user_can( (int) $user_id, 'edit_post', (int) $post_id );
	}
}
