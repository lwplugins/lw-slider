<?php
/**
 * Plugin Name:       LW Slider
 * Plugin URI:        https://github.com/lwplugins/lw-slider
 * Description:       Lightweight responsive slider for WordPress.
 * Version:           1.0.2
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

define( 'LW_SLIDER_VERSION', '1.0.2' );
define( 'LW_SLIDER_FILE', __FILE__ );
define( 'LW_SLIDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'LW_SLIDER_URL', plugin_dir_url( __FILE__ ) );

// Autoloader.
if ( file_exists( LW_SLIDER_PATH . 'vendor/autoload.php' ) ) {
	require_once LW_SLIDER_PATH . 'vendor/autoload.php';
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
