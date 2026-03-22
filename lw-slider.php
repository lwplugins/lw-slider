<?php
/**
 * Plugin Name:       LW Slider
 * Plugin URI:        https://github.com/lwplugins/lw-slider
 * Description:       Lightweight responsive slider for WordPress.
 * Version:           1.0.6
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            LW Plugins
 * Author URI:        https://lwplugins.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lw-slider
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LW_SLIDER_VERSION', '1.0.6' );
define( 'LW_SLIDER_FILE', __FILE__ );
define( 'LW_SLIDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'LW_SLIDER_URL', plugin_dir_url( __FILE__ ) );

// Autoloader: local vendor (standalone/ZIP) or root Composer (dependency install).
if ( file_exists( LW_SLIDER_PATH . 'vendor/autoload.php' ) ) {
	require_once LW_SLIDER_PATH . 'vendor/autoload.php';
} elseif ( ! class_exists( Plugin::class ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			printf(
				'<div class="notice notice-error"><p><strong>LW Slider:</strong> %s</p></div>',
				esc_html__( 'Autoloader not found. Please run "composer install" in the plugin directory, or re-install the plugin from a release ZIP.', 'lw-slider' )
			);
		}
	);
	return;
}

/**
 * Initialize plugin.
 *
 * @return Plugin
 */
function lw_slider(): Plugin {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new Plugin();
	}

	return $instance;
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\lw_slider' );
