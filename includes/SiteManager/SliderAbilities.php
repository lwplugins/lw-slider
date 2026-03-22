<?php
/**
 * Slider Ability Definitions for LW Site Manager.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\SiteManager;

/**
 * Registers Slider-specific abilities with the WordPress Abilities API.
 */
final class SliderAbilities {

	/**
	 * Register all Slider abilities.
	 *
	 * @param object $permissions Permission manager instance.
	 * @return void
	 */
	public static function register( object $permissions ): void {
		self::register_list_sliders( $permissions );
		self::register_get_slider( $permissions );
	}

	/**
	 * Register list-sliders ability.
	 *
	 * @param object $permissions Permission manager instance.
	 * @return void
	 */
	private static function register_list_sliders( object $permissions ): void {
		wp_register_ability(
			'lw-slider/list-sliders',
			array(
				'label'               => __( 'List Sliders', 'lw-slider' ),
				'description'         => __( 'List all sliders with their ID, title, status, and slide count.', 'lw-slider' ),
				'category'            => 'slider',
				'execute_callback'    => array( SliderService::class, 'list_sliders' ),
				'permission_callback' => $permissions->callback( 'can_manage_options' ),
				'input_schema'        => array(
					'type'    => 'object',
					'default' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'sliders' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'id'          => array( 'type' => 'integer' ),
									'title'       => array( 'type' => 'string' ),
									'status'      => array( 'type' => 'string' ),
									'slide_count' => array( 'type' => 'integer' ),
									'shortcode'   => array( 'type' => 'string' ),
								),
							),
						),
					),
				),
				'meta'                => self::readonly_meta(),
			)
		);
	}

	/**
	 * Register get-slider ability.
	 *
	 * @param object $permissions Permission manager instance.
	 * @return void
	 */
	private static function register_get_slider( object $permissions ): void {
		wp_register_ability(
			'lw-slider/get-slider',
			array(
				'label'               => __( 'Get Slider', 'lw-slider' ),
				'description'         => __( 'Get slider details including settings and all slides.', 'lw-slider' ),
				'category'            => 'slider',
				'execute_callback'    => array( SliderService::class, 'get_slider' ),
				'permission_callback' => $permissions->callback( 'can_manage_options' ),
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'Slider post ID.', 'lw-slider' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'slider'  => array(
							'type'       => 'object',
							'properties' => array(
								'id'       => array( 'type' => 'integer' ),
								'title'    => array( 'type' => 'string' ),
								'status'   => array( 'type' => 'string' ),
								'settings' => array( 'type' => 'object' ),
								'slides'   => array(
									'type'  => 'array',
									'items' => array( 'type' => 'object' ),
								),
							),
						),
					),
				),
				'meta'                => self::readonly_meta(),
			)
		);
	}

	/**
	 * Read-only ability metadata.
	 *
	 * @return array<string, mixed>
	 */
	private static function readonly_meta(): array {
		return array(
			'show_in_rest' => true,
			'annotations'  => array(
				'readonly'    => true,
				'destructive' => false,
				'idempotent'  => true,
			),
		);
	}
}
