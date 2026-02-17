<?php
/**
 * Slider meta box registration and asset loading.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin;

use LightweightPlugins\Slider\Admin\MetaBox\SettingsPanel;
use LightweightPlugins\Slider\Admin\MetaBox\SlidesPanel;
use LightweightPlugins\Slider\PostType\SliderPostType;

/**
 * Registers meta boxes and enqueues admin assets for the slider editor.
 */
final class SliderMetaBox {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register meta boxes for the slider post type.
	 *
	 * @return void
	 */
	public function register_meta_boxes(): void {
		add_meta_box(
			'lw-slider-slides',
			__( 'Slides', 'lw-slider' ),
			array( SlidesPanel::class, 'render' ),
			SliderPostType::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'lw-slider-settings',
			__( 'Slider Settings', 'lw-slider' ),
			array( SettingsPanel::class, 'render' ),
			SliderPostType::POST_TYPE,
			'side',
			'default'
		);

		add_meta_box(
			'lw-slider-shortcode',
			__( 'Usage', 'lw-slider' ),
			array( $this, 'render_shortcode_box' ),
			SliderPostType::POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * Render the shortcode/usage meta box.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public function render_shortcode_box( \WP_Post $post ): void {
		if ( 'auto-draft' === $post->post_status ) {
			echo '<p>' . esc_html__( 'Save the slider first to get the shortcode.', 'lw-slider' ) . '</p>';
			return;
		}

		$shortcode = '[lw_slider id="' . $post->ID . '"]';
		?>
		<p><strong><?php esc_html_e( 'Shortcode:', 'lw-slider' ); ?></strong></p>
		<code style="display:block;padding:8px;background:#f0f0f1;user-select:all;cursor:pointer;"><?php echo esc_html( $shortcode ); ?></code>
		<p><strong><?php esc_html_e( 'Gutenberg:', 'lw-slider' ); ?></strong></p>
		<p><?php esc_html_e( 'Use the "LW Slider" block in the editor.', 'lw-slider' ); ?></p>
		<?php
	}

	/**
	 * Enqueue admin assets on slider edit screen only.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || SliderPostType::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_style(
			'lw-slider-admin',
			LW_SLIDER_URL . 'assets/css/admin.css',
			array(),
			LW_SLIDER_VERSION
		);

		wp_enqueue_script(
			'lw-slider-admin',
			LW_SLIDER_URL . 'assets/js/admin.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			LW_SLIDER_VERSION,
			true
		);

		wp_localize_script(
			'lw-slider-admin',
			'lwSliderAdmin',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'lw_slider_admin' ),
				'selectImage' => __( 'Select Image', 'lw-slider' ),
				'useImage'    => __( 'Use this image', 'lw-slider' ),
				'newSlide'    => __( 'New Slide', 'lw-slider' ),
			)
		);
	}
}
