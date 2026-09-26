<?php
/**
 * The slider manager screen.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\Data\SliderSanitizer;
use LightweightPlugins\Slider\Rest\AdminRoutes;
use LightweightPlugins\Slider\Rest\SliderPermissions;

/**
 * "Sliders" under LW Plugins: a mount point for the React admin
 * (build/index), which reads and writes through lw-slider/v1/admin.
 */
final class AppPage {

	/**
	 * Page slug.
	 */
	public const SLUG = 'lw-slider';

	/**
	 * Script and style handle.
	 */
	private const HANDLE = 'lw-slider-admin-app';

	/**
	 * Documentation URL.
	 */
	private const DOCS_URL = 'https://github.com/lwplugins/lw-slider#readme';

	/**
	 * Hook suffix returned by add_submenu_page(). Assets are keyed on it:
	 * WordPress derives its prefix from the translated parent menu title.
	 *
	 * @var string
	 */
	private string $hook_suffix = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'admin_body_class', [ $this, 'body_class' ] );
	}

	/**
	 * The app URL, optionally with a route (e.g. "slider/12").
	 *
	 * @param string $route Hash route without #.
	 * @return string
	 */
	public static function url( string $route = '' ): string {
		return admin_url( 'admin.php?page=' . self::SLUG ) . ( '' !== $route ? '#' . $route : '' );
	}

	/**
	 * Add the page under LW Plugins. Everyone who can edit posts manages
	 * sliders, as with the classic screens.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		ParentPage::maybe_register();

		$hook = add_submenu_page(
			ParentPage::SLUG,
			__( 'Sliders', 'lw-slider' ),
			__( 'Sliders', 'lw-slider' ),
			'edit_posts',
			self::SLUG,
			[ $this, 'render' ]
		);

		$this->hook_suffix = is_string( $hook ) ? $hook : '';
	}

	/**
	 * Enqueue the React app (and the media library) on the screen.
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		if ( '' === $this->hook_suffix || $hook !== $this->hook_suffix ) {
			return;
		}

		if ( ! BuildAssets::enqueue( 'index', self::HANDLE ) ) {
			return;
		}

		wp_enqueue_media();
		wp_add_inline_script( self::HANDLE, 'window.lwSliderAdmin = ' . wp_json_encode( self::boot_data() ) . ';', 'before' );
	}

	/**
	 * What the app needs before its first request.
	 *
	 * @return array<string, mixed>
	 */
	public static function boot_data(): array {
		$settings                       = Defaults::settings();
		$settings['min_height_desktop'] = (int) $settings['min_height_desktop'];
		$settings['min_height_mobile']  = (int) $settings['min_height_mobile'];

		return [
			'version'    => LW_SLIDER_VERSION,
			'namespace'  => AdminRoutes::NAMESPACE,
			'docsUrl'    => self::DOCS_URL,
			'canPublish' => SliderPermissions::can_publish(),
			'defaults'   => [
				'slide'    => Defaults::slide(),
				'settings' => $settings,
			],
			'limits'     => [
				'minHeight'     => SliderSanitizer::MIN_HEIGHT,
				'autoplayDelay' => SliderSanitizer::AUTOPLAY_DELAY,
			],
		];
	}

	/**
	 * Mark the screen body for the app's styles.
	 *
	 * @param string $classes Space-separated body classes.
	 * @return string
	 */
	public function body_class( $classes ): string {
		$classes = (string) $classes;
		$screen  = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( '' === $this->hook_suffix || ! $screen || $screen->id !== $this->hook_suffix ) {
			return $classes;
		}

		return $classes . ' lw-slider-screen';
	}

	/**
	 * Render the mount point (or a notice when the build is missing).
	 *
	 * The mount point sits outside .wrap so core's .wrap margins and
	 * NoticeManager's direct-child notice rules never reach the app; the
	 * missing-build notice carries `lw-notice` so it is not hidden.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! SliderPermissions::can_list() ) {
			return;
		}

		if ( ! BuildAssets::exists( 'index' ) ) {
			printf(
				'<div class="wrap"><h1>%s</h1><div class="notice notice-error lw-notice"><p>%s</p></div></div>',
				esc_html__( 'LW Slider', 'lw-slider' ),
				esc_html__( 'The admin screen files are missing. Re-install the plugin from a release ZIP, or run "npm install && npm run build" in the plugin directory.', 'lw-slider' )
			);
			return;
		}

		echo '<div id="lw-slider-root" class="lw-slider-root"></div>';
	}
}
