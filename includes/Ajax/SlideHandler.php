<?php
/**
 * AJAX handler for slide operations.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Ajax;

/**
 * Handles AJAX requests for slide reordering.
 */
final class SlideHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_lw_slider_reorder_slides', array( $this, 'reorder' ) );
	}

	/**
	 * Handle slide reorder AJAX request.
	 *
	 * @return void
	 */
	public function reorder(): void {
		check_ajax_referer( 'lw_slider_admin', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'lw-slider' ) );
		}

		$post_id = absint( $_POST['post_id'] ?? 0 );
		$order   = array_map( 'absint', (array) ( $_POST['order'] ?? array() ) );

		if ( ! $post_id || empty( $order ) ) {
			wp_send_json_error( __( 'Invalid data.', 'lw-slider' ) );
		}

		$slides = get_post_meta( $post_id, '_lw_slider_slides', true );

		if ( ! is_array( $slides ) ) {
			wp_send_json_error( __( 'No slides found.', 'lw-slider' ) );
		}

		$reordered = array();

		foreach ( $order as $old_index ) {
			if ( isset( $slides[ $old_index ] ) ) {
				$reordered[] = $slides[ $old_index ];
			}
		}

		update_post_meta( $post_id, '_lw_slider_slides', $reordered );

		wp_send_json_success();
	}
}
