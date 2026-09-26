<?php
/**
 * Slider meta registration.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\PostType;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\Data\SliderRepository;
use LightweightPlugins\Slider\Data\SliderSanitizer;

/**
 * Registers the two slider meta keys. Every write, from any code path
 * (update_post_meta, the REST API, the custom fields box), runs through
 * the shared sanitizer, and only users who may edit the slider may change
 * them. In the REST API they appear in the edit context only.
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
				'show_in_rest'      => [
					'schema' => [
						'type'    => 'array',
						'context' => [ 'edit' ],
						'items'   => [
							'type'                 => 'object',
							'properties'           => self::slide_properties(),
							'additionalProperties' => false,
						],
					],
				],
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
				'show_in_rest'      => [
					'schema' => [
						'type'                 => 'object',
						'context'              => [ 'edit' ],
						'properties'           => self::settings_properties(),
						'additionalProperties' => false,
					],
				],
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

	/**
	 * REST schema of one slide.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function slide_properties(): array {
		$text = [ 'type' => 'string' ];

		return [
			'title'           => $text,
			'active'          => [ 'type' => 'boolean' ],
			'bg_type'         => [
				'type' => 'string',
				'enum' => SliderSanitizer::BG_TYPES,
			],
			'bg_image_id'     => [
				'type'    => 'integer',
				'minimum' => 0,
			],
			'bg_color'        => $text,
			'bg_position'     => [
				'type' => 'string',
				'enum' => array_keys( Defaults::bg_positions() ),
			],
			'overlay_color'   => $text,
			'overlay_opacity' => [
				'type'    => 'integer',
				'minimum' => 0,
				'maximum' => 100,
			],
			'headline'        => $text,
			'subheadline'     => $text,
			'description'     => $text,
			'link_url'        => $text,
			'link_target'     => [
				'type' => 'string',
				'enum' => SliderSanitizer::LINK_TARGETS,
			],
			'cta_mode'        => [
				'type' => 'string',
				'enum' => SliderSanitizer::CTA_MODES,
			],
			'button_text'     => $text,
			'image_alt'       => $text,
		];
	}

	/**
	 * REST schema of the settings.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function settings_properties(): array {
		$bool   = [ 'type' => 'boolean' ];
		$height = [ 'type' => [ 'string', 'integer' ] ];

		return [
			'min_height_desktop' => $height,
			'min_height_mobile'  => $height,
			'dots'               => $bool,
			'arrows'             => $bool,
			'arrows_mobile'      => $bool,
			'autoplay'           => $bool,
			'autoplay_delay'     => [
				'type'    => 'integer',
				'minimum' => SliderSanitizer::AUTOPLAY_DELAY[0],
				'maximum' => SliderSanitizer::AUTOPLAY_DELAY[1],
			],
			'transition'         => [
				'type' => 'string',
				'enum' => array_keys( Defaults::transitions() ),
			],
			'loop'               => $bool,
			'content_align_h'    => [
				'type' => 'string',
				'enum' => SliderSanitizer::ALIGNS_H,
			],
			'content_align_v'    => [
				'type' => 'string',
				'enum' => SliderSanitizer::ALIGNS_V,
			],
			'use_default_styles' => $bool,
			'custom_class'       => [ 'type' => 'string' ],
			'swipe'              => $bool,
			'keyboard'           => $bool,
			'pause_on_hover'     => $bool,
			'hide_on_mobile'     => $bool,
		];
	}
}
