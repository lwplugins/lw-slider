<?php
/**
 * LW Site Manager Integration.
 *
 * Registers Slider abilities when LW Site Manager is active.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\SiteManager;

/**
 * Hooks into LW Site Manager to register Slider abilities.
 */
final class Integration {

	/**
	 * Initialize hooks. Safe to call even if Site Manager is not active.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'lw_site_manager_register_categories', array( self::class, 'register_category' ) );
		add_action( 'lw_site_manager_register_abilities', array( self::class, 'register_abilities' ) );
	}

	/**
	 * Register the Slider ability category.
	 *
	 * @return void
	 */
	public static function register_category(): void {
		wp_register_ability_category(
			'slider',
			array(
				'label'       => __( 'Slider', 'lw-slider' ),
				'description' => __( 'Slider management abilities', 'lw-slider' ),
			)
		);
	}

	/**
	 * Register Slider abilities.
	 *
	 * @param object $permissions Permission manager from Site Manager.
	 * @return void
	 */
	public static function register_abilities( object $permissions ): void {
		SliderAbilities::register( $permissions );
	}
}
