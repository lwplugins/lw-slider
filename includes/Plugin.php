<?php
/**
 * Main Plugin class.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider;

use LightweightPlugins\Slider\Admin\ParentPage;
use LightweightPlugins\Slider\Admin\SliderColumns;
use LightweightPlugins\Slider\Admin\SliderDuplicator;
use LightweightPlugins\Slider\Admin\SliderMetaBox;
use LightweightPlugins\Slider\Admin\SliderSaveHandler;
use LightweightPlugins\Slider\Ajax\SlideHandler;
use LightweightPlugins\Slider\Block\SliderBlock;
use LightweightPlugins\Slider\Frontend\Assets;
use LightweightPlugins\Slider\Frontend\Shortcode;
use LightweightPlugins\Slider\PostType\SliderPostType;

/**
 * Main plugin class.
 */
final class Plugin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
		$this->init_post_type();
		$this->init_admin();
		$this->init_frontend();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( LW_SLIDER_FILE ),
			array( $this, 'add_settings_link' )
		);
	}

	/**
	 * Initialize post type.
	 *
	 * @return void
	 */
	private function init_post_type(): void {
		add_action( 'init', array( SliderPostType::class, 'register' ) );
	}

	/**
	 * Initialize admin.
	 *
	 * @return void
	 */
	private function init_admin(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( ParentPage::class, 'maybe_register' ) );
		new SliderMetaBox();
		new SliderColumns();
		new SliderDuplicator();
		new SliderSaveHandler();
		new SlideHandler();
	}

	/**
	 * Initialize frontend.
	 *
	 * @return void
	 */
	private function init_frontend(): void {
		new Shortcode();
		new Assets();
		new SliderBlock();
	}

	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'lw-slider',
			false,
			dirname( plugin_basename( LW_SLIDER_FILE ) ) . '/languages'
		);
	}

	/**
	 * Add settings link.
	 *
	 * @param array<string> $links Plugin links.
	 * @return array<string>
	 */
	public function add_settings_link( array $links ): array {
		$url  = admin_url( 'edit.php?post_type=lw-slider' );
		$link = '<a href="' . esc_url( $url ) . '">' . __( 'Sliders', 'lw-slider' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}
}
