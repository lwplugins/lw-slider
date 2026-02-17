<?php
/**
 * Slide field renderer trait.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin\MetaBox;

use LightweightPlugins\Slider\Data\Defaults;

/**
 * Renders individual slide form fields.
 */
trait SlideFieldsTrait {

	/**
	 * Render a text input field.
	 *
	 * @param string $name  Field name suffix.
	 * @param string $label Field label.
	 * @param string $value Current value.
	 * @param int    $index Slide index.
	 * @return void
	 */
	private static function render_text_field( string $name, string $label, string $value, int $index ): void {
		$field_name = "lw_slider_slides[{$index}][{$name}]";
		$field_id   = "lw-slide-{$index}-{$name}";
		?>
		<p>
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label><br>
			<input type="text" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Render a textarea field.
	 *
	 * @param string $name  Field name suffix.
	 * @param string $label Field label.
	 * @param string $value Current value.
	 * @param int    $index Slide index.
	 * @return void
	 */
	private static function render_textarea_field( string $name, string $label, string $value, int $index ): void {
		$field_name = "lw_slider_slides[{$index}][{$name}]";
		$field_id   = "lw-slide-{$index}-{$name}";
		?>
		<p>
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label><br>
			<textarea id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" class="widefat" rows="3"><?php echo esc_textarea( $value ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * Render a select field.
	 *
	 * @param string                $name    Field name suffix.
	 * @param string                $label   Field label.
	 * @param string                $value   Current value.
	 * @param int                   $index   Slide index.
	 * @param array<string, string> $options Options (value => label).
	 * @return void
	 */
	private static function render_select_field( string $name, string $label, string $value, int $index, array $options ): void {
		$field_name = "lw_slider_slides[{$index}][{$name}]";
		$field_id   = "lw-slide-{$index}-{$name}";
		?>
		<p>
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label><br>
			<select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" class="widefat">
				<?php foreach ( $options as $opt_value => $opt_label ) : ?>
					<option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $value, $opt_value ); ?>><?php echo esc_html( $opt_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}
}
