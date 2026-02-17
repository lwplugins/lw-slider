<?php
/**
 * Slider duplicator.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin;

use LightweightPlugins\Slider\PostType\SliderPostType;

/**
 * Adds a "Duplicate" row action to the slider list table.
 */
final class SliderDuplicator {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'post_row_actions', array( $this, 'add_row_action' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'handle_duplicate' ) );
	}

	/**
	 * Add duplicate link to row actions.
	 *
	 * @param array<string, string> $actions Row actions.
	 * @param \WP_Post              $post    Current post.
	 * @return array<string, string>
	 */
	public function add_row_action( array $actions, \WP_Post $post ): array {
		if ( SliderPostType::POST_TYPE !== $post->post_type ) {
			return $actions;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			admin_url( 'edit.php?post_type=' . SliderPostType::POST_TYPE . '&action=lw_duplicate_slider&post=' . $post->ID ),
			'lw_duplicate_slider_' . $post->ID
		);

		$actions['lw_duplicate'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $url ),
			esc_html__( 'Duplicate', 'lw-slider' )
		);

		return $actions;
	}

	/**
	 * Handle the duplicate action.
	 *
	 * @return void
	 */
	public function handle_duplicate(): void {
		if ( empty( $_GET['action'] ) || 'lw_duplicate_slider' !== $_GET['action'] ) {
			return;
		}

		$post_id = absint( $_GET['post'] ?? 0 );

		if ( ! $post_id ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'lw_duplicate_slider_' . $post_id ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'lw-slider' ) );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'lw-slider' ) );
		}

		$original = get_post( $post_id );

		if ( ! $original || SliderPostType::POST_TYPE !== $original->post_type ) {
			wp_die( esc_html__( 'Slider not found.', 'lw-slider' ) );
		}

		$new_id = wp_insert_post(
			array(
				'post_title'  => $original->post_title . ' ' . __( '(Copy)', 'lw-slider' ),
				'post_type'   => SliderPostType::POST_TYPE,
				'post_status' => 'draft',
			)
		);

		if ( is_wp_error( $new_id ) ) {
			wp_die( esc_html__( 'Failed to duplicate slider.', 'lw-slider' ) );
		}

		$slides   = get_post_meta( $post_id, '_lw_slider_slides', true );
		$settings = get_post_meta( $post_id, '_lw_slider_settings', true );

		if ( $slides ) {
			update_post_meta( $new_id, '_lw_slider_slides', $slides );
		}

		if ( $settings ) {
			update_post_meta( $new_id, '_lw_slider_settings', $settings );
		}

		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
		exit;
	}
}
