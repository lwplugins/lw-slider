<?php
/**
 * Default values for slides and slider settings.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Data;

/**
 * Provides default values for slides and slider settings.
 */
final class Defaults {

	/**
	 * Get default slide data.
	 *
	 * @return array<string, mixed>
	 */
	public static function slide(): array {
		return array(
			'title'           => '',
			'active'          => true,
			'bg_type'         => 'image',
			'bg_image_id'     => 0,
			'bg_color'        => '#f0f0f0',
			'bg_position'     => 'center center',
			'overlay_color'   => '',
			'overlay_opacity' => 50,
			'headline'        => '',
			'subheadline'     => '',
			'description'     => '',
			'link_url'        => '',
			'link_target'     => '_self',
			'cta_mode'        => 'full_slide',
			'button_text'     => '',
			'image_alt'       => '',
		);
	}

	/**
	 * Get default slider settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function settings(): array {
		return array(
			'min_height_desktop' => '400',
			'min_height_mobile'  => '280',
			'dots'               => true,
			'arrows'             => true,
			'arrows_mobile'      => false,
			'autoplay'           => false,
			'autoplay_delay'     => 5000,
			'transition'         => 'slide',
			'loop'               => true,
			'content_align_h'    => 'center',
			'content_align_v'    => 'center',
			'use_default_styles' => true,
			'custom_class'       => '',
			'swipe'              => true,
			'keyboard'           => true,
			'pause_on_hover'     => false,
		);
	}

	/**
	 * Get allowed background position values.
	 *
	 * @return array<string, string>
	 */
	public static function bg_positions(): array {
		return array(
			'left top'      => __( 'Left Top', 'lw-slider' ),
			'center top'    => __( 'Center Top', 'lw-slider' ),
			'right top'     => __( 'Right Top', 'lw-slider' ),
			'left center'   => __( 'Left Center', 'lw-slider' ),
			'center center' => __( 'Center Center', 'lw-slider' ),
			'right center'  => __( 'Right Center', 'lw-slider' ),
			'left bottom'   => __( 'Left Bottom', 'lw-slider' ),
			'center bottom' => __( 'Center Bottom', 'lw-slider' ),
			'right bottom'  => __( 'Right Bottom', 'lw-slider' ),
		);
	}

	/**
	 * Get allowed transition types.
	 *
	 * @return array<string, string>
	 */
	public static function transitions(): array {
		return array(
			'slide' => __( 'Slide', 'lw-slider' ),
			'fade'  => __( 'Fade', 'lw-slider' ),
		);
	}
}
