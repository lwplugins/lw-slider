<?php
/**
 * Slide section renderers trait.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin\MetaBox;

use LightweightPlugins\Slider\Data\Defaults;

/**
 * Renders slide form sections: background, overlay, and CTA.
 */
trait SlideSectionsTrait {

	/**
	 * Render background section.
	 *
	 * @param int                  $index Slide index.
	 * @param array<string, mixed> $slide Slide data.
	 * @return void
	 */
	private static function render_bg_section( int $index, array $slide ): void {
		$bg_type     = (string) $slide['bg_type'];
		$name_prefix = "lw_slider_slides[{$index}]";
		?>
		<fieldset class="lw-slide-section">
			<legend><?php esc_html_e( 'Background', 'lw-slider' ); ?></legend>
			<p>
				<label><input type="radio" name="<?php echo esc_attr( $name_prefix ); ?>[bg_type]" value="image" <?php checked( $bg_type, 'image' ); ?> class="lw-bg-type-radio"> <?php esc_html_e( 'Image', 'lw-slider' ); ?></label>
				<label style="margin-left:15px;"><input type="radio" name="<?php echo esc_attr( $name_prefix ); ?>[bg_type]" value="color" <?php checked( $bg_type, 'color' ); ?> class="lw-bg-type-radio"> <?php esc_html_e( 'Color', 'lw-slider' ); ?></label>
			</p>
			<div class="lw-bg-image-fields" <?php echo 'color' === $bg_type ? 'style="display:none;"' : ''; ?>>
				<input type="hidden" name="<?php echo esc_attr( $name_prefix ); ?>[bg_image_id]" value="<?php echo esc_attr( (string) $slide['bg_image_id'] ); ?>" class="lw-bg-image-id">
				<div class="lw-bg-image-preview">
					<?php
					if ( ! empty( $slide['bg_image_id'] ) ) {
						$img = wp_get_attachment_image_url( (int) $slide['bg_image_id'], 'medium' );
						if ( $img ) {
							echo '<img src="' . esc_url( $img ) . '" alt="" style="max-width:200px;height:auto;">';
						}
					}
					?>
				</div>
				<button type="button" class="button lw-bg-image-select"><?php esc_html_e( 'Select Image', 'lw-slider' ); ?></button>
				<button type="button" class="button lw-bg-image-remove" <?php echo empty( $slide['bg_image_id'] ) ? 'style="display:none;"' : ''; ?>><?php esc_html_e( 'Remove', 'lw-slider' ); ?></button>
				<?php
				self::render_select_field( 'bg_position', __( 'Position', 'lw-slider' ), (string) $slide['bg_position'], $index, Defaults::bg_positions() );
				self::render_text_field( 'image_alt', __( 'Alt Text', 'lw-slider' ), (string) $slide['image_alt'], $index );
				?>
			</div>
			<div class="lw-bg-color-fields" <?php echo 'image' === $bg_type ? 'style="display:none;"' : ''; ?>>
				<p>
					<label for="lw-slide-<?php echo esc_attr( (string) $index ); ?>-bg_color"><?php esc_html_e( 'Background Color', 'lw-slider' ); ?></label><br>
					<input type="color" id="lw-slide-<?php echo esc_attr( (string) $index ); ?>-bg_color" name="<?php echo esc_attr( $name_prefix ); ?>[bg_color]" value="<?php echo esc_attr( (string) $slide['bg_color'] ); ?>">
				</p>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * Render overlay section.
	 *
	 * @param int                  $index Slide index.
	 * @param array<string, mixed> $slide Slide data.
	 * @return void
	 */
	private static function render_overlay_section( int $index, array $slide ): void {
		$name_prefix = "lw_slider_slides[{$index}]";
		?>
		<fieldset class="lw-slide-section">
			<legend><?php esc_html_e( 'Overlay', 'lw-slider' ); ?></legend>
			<p>
				<label for="lw-slide-<?php echo esc_attr( (string) $index ); ?>-overlay_color"><?php esc_html_e( 'Color', 'lw-slider' ); ?></label><br>
				<input type="color" id="lw-slide-<?php echo esc_attr( (string) $index ); ?>-overlay_color" name="<?php echo esc_attr( $name_prefix ); ?>[overlay_color]" value="<?php echo esc_attr( (string) $slide['overlay_color'] ); ?>">
			</p>
			<p>
				<label for="lw-slide-<?php echo esc_attr( (string) $index ); ?>-overlay_opacity"><?php esc_html_e( 'Opacity', 'lw-slider' ); ?> (<span class="lw-opacity-value"><?php echo esc_html( (string) $slide['overlay_opacity'] ); ?></span>%)</label><br>
				<input type="range" id="lw-slide-<?php echo esc_attr( (string) $index ); ?>-overlay_opacity" name="<?php echo esc_attr( $name_prefix ); ?>[overlay_opacity]" value="<?php echo esc_attr( (string) $slide['overlay_opacity'] ); ?>" min="0" max="100" class="lw-opacity-range">
			</p>
		</fieldset>
		<?php
	}

	/**
	 * Render CTA section.
	 *
	 * @param int                  $index Slide index.
	 * @param array<string, mixed> $slide Slide data.
	 * @return void
	 */
	private static function render_cta_section( int $index, array $slide ): void {
		$cta_mode    = (string) $slide['cta_mode'];
		$name_prefix = "lw_slider_slides[{$index}]";
		?>
		<fieldset class="lw-slide-section">
			<legend><?php esc_html_e( 'Call to Action', 'lw-slider' ); ?></legend>
			<p>
				<label><input type="radio" name="<?php echo esc_attr( $name_prefix ); ?>[cta_mode]" value="full_slide" <?php checked( $cta_mode, 'full_slide' ); ?> class="lw-cta-mode-radio"> <?php esc_html_e( 'Full slide link', 'lw-slider' ); ?></label>
				<label style="margin-left:15px;"><input type="radio" name="<?php echo esc_attr( $name_prefix ); ?>[cta_mode]" value="button" <?php checked( $cta_mode, 'button' ); ?> class="lw-cta-mode-radio"> <?php esc_html_e( 'Button', 'lw-slider' ); ?></label>
			</p>
			<?php
			self::render_text_field( 'link_url', __( 'Link URL', 'lw-slider' ), (string) $slide['link_url'], $index );
			self::render_select_field(
				'link_target',
				__( 'Link Target', 'lw-slider' ),
				(string) $slide['link_target'],
				$index,
				array(
					'_self'  => __( 'Same window', 'lw-slider' ),
					'_blank' => __( 'New window', 'lw-slider' ),
				)
			);
			?>
			<div class="lw-cta-button-fields" <?php echo 'full_slide' === $cta_mode ? 'style="display:none;"' : ''; ?>>
				<?php self::render_text_field( 'button_text', __( 'Button Text', 'lw-slider' ), (string) $slide['button_text'], $index ); ?>
			</div>
		</fieldset>
		<?php
	}
}
