<?php
/**
 * Slides meta box panel.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin\MetaBox;

use LightweightPlugins\Slider\Data\Defaults;

/**
 * Renders the slides editor panel inside the meta box.
 */
final class SlidesPanel {

	use SlideFieldsTrait;
	use SlideSectionsTrait;

	/**
	 * Render the slides panel.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public static function render( \WP_Post $post ): void {
		wp_nonce_field( 'lw_slider_save', 'lw_slider_nonce' );

		$slides = get_post_meta( $post->ID, '_lw_slider_slides', true );

		if ( ! is_array( $slides ) ) {
			$slides = array();
		}

		?>
		<div id="lw-slider-slides-wrap">
			<div id="lw-slider-slides-list" class="lw-slides-sortable">
				<?php
				foreach ( $slides as $index => $slide ) {
					$slide = wp_parse_args( $slide, Defaults::slide() );
					self::render_slide_card( (int) $index, $slide );
				}
				?>
			</div>

			<p>
				<button type="button" class="button button-secondary" id="lw-slider-add-slide">
					<?php esc_html_e( '+ Add Slide', 'lw-slider' ); ?>
				</button>
			</p>
		</div>

		<?php self::render_slide_template(); ?>
		<?php
	}

	/**
	 * Render a single slide card.
	 *
	 * @param int                  $index Slide index.
	 * @param array<string, mixed> $slide Slide data.
	 * @return void
	 */
	public static function render_slide_card( int $index, array $slide ): void {
		$thumb_url = '';

		if ( ! empty( $slide['bg_image_id'] ) ) {
			$img = wp_get_attachment_image_url( (int) $slide['bg_image_id'], 'thumbnail' );

			if ( $img ) {
				$thumb_url = $img;
			}
		}

		$is_active = ! empty( $slide['active'] );
		$title     = ! empty( $slide['headline'] ) ? $slide['headline'] : __( 'Slide', 'lw-slider' ) . ' ' . ( $index + 1 );
		?>
		<div class="lw-slide-card <?php echo $is_active ? '' : 'lw-slide-inactive'; ?>" data-index="<?php echo esc_attr( (string) $index ); ?>">
			<div class="lw-slide-header">
				<span class="lw-slide-drag dashicons dashicons-move" title="<?php esc_attr_e( 'Drag to reorder', 'lw-slider' ); ?>"></span>
				<?php if ( $thumb_url ) : ?>
					<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" class="lw-slide-thumb">
				<?php endif; ?>
				<span class="lw-slide-title"><?php echo esc_html( $title ); ?></span>
				<label class="lw-slide-active-toggle">
					<input type="hidden" name="lw_slider_slides[<?php echo esc_attr( (string) $index ); ?>][active]" value="0">
					<input type="checkbox" name="lw_slider_slides[<?php echo esc_attr( (string) $index ); ?>][active]" value="1" <?php checked( $is_active ); ?>>
					<?php esc_html_e( 'Active', 'lw-slider' ); ?>
				</label>
				<button type="button" class="lw-slide-duplicate button-link" title="<?php esc_attr_e( 'Duplicate', 'lw-slider' ); ?>">
					<span class="dashicons dashicons-admin-page"></span>
				</button>
				<button type="button" class="lw-slide-toggle button-link" title="<?php esc_attr_e( 'Expand/Collapse', 'lw-slider' ); ?>">
					<span class="dashicons dashicons-arrow-down-alt2"></span>
				</button>
				<button type="button" class="lw-slide-remove button-link" title="<?php esc_attr_e( 'Remove', 'lw-slider' ); ?>">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>

			<div class="lw-slide-body" style="display:none;">
				<?php self::render_slide_fields( $index, $slide ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render all fields for a slide.
	 *
	 * @param int                  $index Slide index.
	 * @param array<string, mixed> $slide Slide data.
	 * @return void
	 */
	private static function render_slide_fields( int $index, array $slide ): void {
		self::render_bg_section( $index, $slide );
		self::render_text_field( 'headline', __( 'Headline', 'lw-slider' ), (string) $slide['headline'], $index );
		self::render_text_field( 'subheadline', __( 'Subheadline', 'lw-slider' ), (string) $slide['subheadline'], $index );
		self::render_textarea_field( 'description', __( 'Description', 'lw-slider' ), (string) $slide['description'], $index );
		self::render_overlay_section( $index, $slide );
		self::render_cta_section( $index, $slide );
	}

	/**
	 * Render the JS template for new slides.
	 *
	 * @return void
	 */
	private static function render_slide_template(): void {
		$defaults = Defaults::slide();
		?>
		<script type="text/html" id="tmpl-lw-slider-slide">
			<?php self::render_slide_card( 999, $defaults ); ?>
		</script>
		<?php
	}
}
