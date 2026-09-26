<?php
/**
 * Slider save handler.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin;

use LightweightPlugins\Slider\Data\SliderRepository;
use LightweightPlugins\Slider\Data\SliderSanitizer;
use LightweightPlugins\Slider\PostType\SliderPostType;

/**
 * Handles saving slider data on save_post.
 */
final class SliderSaveHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'save_post_' . SliderPostType::POST_TYPE, [ $this, 'save' ] );
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
	 * Save slides data. A form without slides stores an empty list.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function save_slides( int $post_id ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in can_save(); SliderSanitizer sanitizes every field.
		$raw = isset( $_POST['lw_slider_slides'] ) ? wp_unslash( (array) $_POST['lw_slider_slides'] ) : [];

		SliderRepository::save_slides( $post_id, SliderSanitizer::slides( $raw ) );
	}

	/**
	 * Save slider settings (left untouched when the form has none).
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function save_settings( int $post_id ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in can_save().
		if ( ! isset( $_POST['lw_slider_settings'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in can_save(); SliderSanitizer sanitizes every field.
		$raw = wp_unslash( (array) $_POST['lw_slider_settings'] );

		SliderRepository::save_settings( $post_id, SliderSanitizer::settings( $raw ) );
	}
}
