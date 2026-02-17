<?php
/**
 * Slider settings meta box panel.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin\MetaBox;

use LightweightPlugins\Slider\Data\Defaults;

/**
 * Renders the slider settings panel inside the side meta box.
 */
final class SettingsPanel {

	/**
	 * Render the settings panel.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public static function render( \WP_Post $post ): void {
		$settings = get_post_meta( $post->ID, '_lw_slider_settings', true );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$settings = wp_parse_args( $settings, Defaults::settings() );

		self::render_dimensions( $settings );
		self::render_navigation( $settings );
		self::render_behavior( $settings );
		self::render_content( $settings );
		self::render_advanced( $settings );
	}

	/**
	 * Render dimension fields.
	 *
	 * @param array<string, mixed> $s Settings.
	 * @return void
	 */
	private static function render_dimensions( array $s ): void {
		?>
		<p><strong><?php esc_html_e( 'Dimensions', 'lw-slider' ); ?></strong></p>
		<p>
			<label for="lw-settings-min-height-desktop"><?php esc_html_e( 'Min Height Desktop (px)', 'lw-slider' ); ?></label><br>
			<input type="number" id="lw-settings-min-height-desktop" name="lw_slider_settings[min_height_desktop]" value="<?php echo esc_attr( (string) $s['min_height_desktop'] ); ?>" min="100" max="1200" class="small-text">
		</p>
		<p>
			<label for="lw-settings-min-height-mobile"><?php esc_html_e( 'Min Height Mobile (px)', 'lw-slider' ); ?></label><br>
			<input type="number" id="lw-settings-min-height-mobile" name="lw_slider_settings[min_height_mobile]" value="<?php echo esc_attr( (string) $s['min_height_mobile'] ); ?>" min="100" max="1200" class="small-text">
		</p>
		<?php
	}

	/**
	 * Render navigation fields.
	 *
	 * @param array<string, mixed> $s Settings.
	 * @return void
	 */
	private static function render_navigation( array $s ): void {
		?>
		<p><strong><?php esc_html_e( 'Navigation', 'lw-slider' ); ?></strong></p>
		<?php
		self::render_checkbox( 'dots', __( 'Show dots', 'lw-slider' ), $s );
		self::render_checkbox( 'arrows', __( 'Show arrows', 'lw-slider' ), $s );
		self::render_checkbox( 'arrows_mobile', __( 'Show arrows on mobile', 'lw-slider' ), $s );
	}

	/**
	 * Render behavior fields.
	 *
	 * @param array<string, mixed> $s Settings.
	 * @return void
	 */
	private static function render_behavior( array $s ): void {
		?>
		<p><strong><?php esc_html_e( 'Behavior', 'lw-slider' ); ?></strong></p>
		<?php
		self::render_checkbox( 'autoplay', __( 'Autoplay', 'lw-slider' ), $s );
		?>
		<p class="lw-autoplay-delay-field" <?php echo empty( $s['autoplay'] ) ? 'style="display:none;"' : ''; ?>>
			<label for="lw-settings-autoplay-delay"><?php esc_html_e( 'Delay (ms)', 'lw-slider' ); ?></label><br>
			<input type="number" id="lw-settings-autoplay-delay" name="lw_slider_settings[autoplay_delay]" value="<?php echo esc_attr( (string) $s['autoplay_delay'] ); ?>" min="1000" max="30000" step="500" class="small-text">
		</p>
		<p>
			<label for="lw-settings-transition"><?php esc_html_e( 'Transition', 'lw-slider' ); ?></label><br>
			<select id="lw-settings-transition" name="lw_slider_settings[transition]">
				<?php foreach ( Defaults::transitions() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $s['transition'], $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
		self::render_checkbox( 'loop', __( 'Loop', 'lw-slider' ), $s );
		self::render_checkbox( 'pause_on_hover', __( 'Pause on hover', 'lw-slider' ), $s );
		self::render_checkbox( 'swipe', __( 'Swipe', 'lw-slider' ), $s );
		self::render_checkbox( 'keyboard', __( 'Keyboard navigation', 'lw-slider' ), $s );
	}

	/**
	 * Render content alignment fields.
	 *
	 * @param array<string, mixed> $s Settings.
	 * @return void
	 */
	private static function render_content( array $s ): void {
		$h_options = array(
			'left'   => __( 'Left', 'lw-slider' ),
			'center' => __( 'Center', 'lw-slider' ),
			'right'  => __( 'Right', 'lw-slider' ),
		);

		$v_options = array(
			'top'    => __( 'Top', 'lw-slider' ),
			'center' => __( 'Center', 'lw-slider' ),
			'bottom' => __( 'Bottom', 'lw-slider' ),
		);

		?>
		<p><strong><?php esc_html_e( 'Content Alignment', 'lw-slider' ); ?></strong></p>
		<p>
			<label for="lw-settings-align-h"><?php esc_html_e( 'Horizontal', 'lw-slider' ); ?></label><br>
			<select id="lw-settings-align-h" name="lw_slider_settings[content_align_h]">
				<?php foreach ( $h_options as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $s['content_align_h'], $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="lw-settings-align-v"><?php esc_html_e( 'Vertical', 'lw-slider' ); ?></label><br>
			<select id="lw-settings-align-v" name="lw_slider_settings[content_align_v]">
				<?php foreach ( $v_options as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $s['content_align_v'], $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * Render advanced fields.
	 *
	 * @param array<string, mixed> $s Settings.
	 * @return void
	 */
	private static function render_advanced( array $s ): void {
		?>
		<p><strong><?php esc_html_e( 'Advanced', 'lw-slider' ); ?></strong></p>
		<?php self::render_checkbox( 'use_default_styles', __( 'Use default styles', 'lw-slider' ), $s ); ?>
		<p>
			<label for="lw-settings-custom-class"><?php esc_html_e( 'Custom CSS Class', 'lw-slider' ); ?></label><br>
			<input type="text" id="lw-settings-custom-class" name="lw_slider_settings[custom_class]" value="<?php echo esc_attr( (string) $s['custom_class'] ); ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Render a checkbox field.
	 *
	 * @param string               $name  Setting key.
	 * @param string               $label Checkbox label.
	 * @param array<string, mixed> $s     Settings.
	 * @return void
	 */
	private static function render_checkbox( string $name, string $label, array $s ): void {
		?>
		<p>
			<input type="hidden" name="lw_slider_settings[<?php echo esc_attr( $name ); ?>]" value="0">
			<label>
				<input type="checkbox" name="lw_slider_settings[<?php echo esc_attr( $name ); ?>]" value="1" <?php checked( ! empty( $s[ $name ] ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</label>
		</p>
		<?php
	}
}
