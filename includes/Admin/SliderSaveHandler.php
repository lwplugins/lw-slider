<?php
/**
 * Slider save handler.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin;

use LightweightPlugins\Slider\Data\Defaults;
use LightweightPlugins\Slider\PostType\SliderPostType;

/**
 * Handles saving slider data on save_post.
 */
final class SliderSaveHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'save_post_' . SliderPostType::POST_TYPE, array( $this, 'save' ) );
	}

	/**
	 * Save slider data.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save( int $post_id ): void {
		if ( ! $this->can_save( $post_id ) ) {
			return;
		}

		$this->save_slides( $post_id );
		$this->save_settings( $post_id );
	}

	/**
	 * Check if we can save.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	private function can_save( int $post_id ): bool {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return wp_verify_nonce(
			sanitize_key( $_POST['lw_slider_nonce'] ?? '' ),
			'lw_slider_save'
		) !== false;
	}

	/**
	 * Save slides data.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function save_slides( int $post_id ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in can_save().
		if ( ! isset( $_POST['lw_slider_slides'] ) ) {
			update_post_meta( $post_id, '_lw_slider_slides', array() );
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verified in can_save(). Sanitized below per field.
		$raw_slides = (array) $_POST['lw_slider_slides'];
		$slides     = array();

		foreach ( $raw_slides as $raw ) {
			$slides[] = $this->sanitize_slide( (array) $raw );
		}

		update_post_meta( $post_id, '_lw_slider_slides', $slides );
	}

	/**
	 * Sanitize a single slide.
	 *
	 * @param array<string, mixed> $raw Raw slide data.
	 * @return array<string, mixed>
	 */
	private function sanitize_slide( array $raw ): array {
		$defaults     = Defaults::slide();
		$bg_types     = array( 'image', 'color' );
		$cta_modes    = array( 'full_slide', 'button' );
		$link_targets = array( '_self', '_blank' );
		$bg_positions = array_keys( Defaults::bg_positions() );

		return array(
			'title'           => sanitize_text_field( $raw['title'] ?? $defaults['title'] ),
			'active'          => ! empty( $raw['active'] ),
			'bg_type'         => in_array( $raw['bg_type'] ?? '', $bg_types, true ) ? $raw['bg_type'] : $defaults['bg_type'],
			'bg_image_id'     => absint( $raw['bg_image_id'] ?? 0 ),
			'bg_color'        => sanitize_hex_color( $raw['bg_color'] ?? '' ) ? sanitize_hex_color( $raw['bg_color'] ?? '' ) : $defaults['bg_color'],
			'bg_position'     => in_array( $raw['bg_position'] ?? '', $bg_positions, true ) ? $raw['bg_position'] : $defaults['bg_position'],
			'overlay_color'   => sanitize_hex_color( $raw['overlay_color'] ?? '' ) ? sanitize_hex_color( $raw['overlay_color'] ?? '' ) : '',
			'overlay_opacity' => min( 100, max( 0, absint( $raw['overlay_opacity'] ?? 50 ) ) ),
			'headline'        => sanitize_text_field( $raw['headline'] ?? '' ),
			'subheadline'     => sanitize_text_field( $raw['subheadline'] ?? '' ),
			'description'     => sanitize_textarea_field( $raw['description'] ?? '' ),
			'link_url'        => esc_url_raw( $raw['link_url'] ?? '' ),
			'link_target'     => in_array( $raw['link_target'] ?? '', $link_targets, true ) ? $raw['link_target'] : $defaults['link_target'],
			'cta_mode'        => in_array( $raw['cta_mode'] ?? '', $cta_modes, true ) ? $raw['cta_mode'] : $defaults['cta_mode'],
			'button_text'     => sanitize_text_field( $raw['button_text'] ?? '' ),
			'image_alt'       => sanitize_text_field( $raw['image_alt'] ?? '' ),
		);
	}

	/**
	 * Save slider settings.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function save_settings( int $post_id ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in can_save().
		if ( ! isset( $_POST['lw_slider_settings'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verified in can_save(). Sanitized below per field.
		$raw      = (array) $_POST['lw_slider_settings'];
		$defaults = Defaults::settings();

		$transitions = array_keys( Defaults::transitions() );
		$aligns_h    = array( 'left', 'center', 'right' );
		$aligns_v    = array( 'top', 'center', 'bottom' );

		$settings = array(
			'min_height_desktop' => (string) min( 1200, max( 100, absint( $raw['min_height_desktop'] ?? 400 ) ) ),
			'min_height_mobile'  => (string) min( 1200, max( 100, absint( $raw['min_height_mobile'] ?? 280 ) ) ),
			'dots'               => ! empty( $raw['dots'] ),
			'arrows'             => ! empty( $raw['arrows'] ),
			'arrows_mobile'      => ! empty( $raw['arrows_mobile'] ),
			'autoplay'           => ! empty( $raw['autoplay'] ),
			'autoplay_delay'     => min( 30000, max( 1000, absint( $raw['autoplay_delay'] ?? 5000 ) ) ),
			'transition'         => in_array( $raw['transition'] ?? '', $transitions, true ) ? $raw['transition'] : $defaults['transition'],
			'loop'               => ! empty( $raw['loop'] ),
			'content_align_h'    => in_array( $raw['content_align_h'] ?? '', $aligns_h, true ) ? $raw['content_align_h'] : $defaults['content_align_h'],
			'content_align_v'    => in_array( $raw['content_align_v'] ?? '', $aligns_v, true ) ? $raw['content_align_v'] : $defaults['content_align_v'],
			'use_default_styles' => ! empty( $raw['use_default_styles'] ),
			'custom_class'       => sanitize_html_class( $raw['custom_class'] ?? '' ),
			'swipe'              => ! empty( $raw['swipe'] ),
			'keyboard'           => ! empty( $raw['keyboard'] ),
			'pause_on_hover'     => ! empty( $raw['pause_on_hover'] ),
			'hide_on_mobile'     => ! empty( $raw['hide_on_mobile'] ),
		);

		update_post_meta( $post_id, '_lw_slider_settings', $settings );
	}
}
