<?php
/**
 * Custom columns for the Slider CPT list table.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin;

use LightweightPlugins\Slider\PostType\SliderPostType;

/**
 * Adds custom columns to the slider list table.
 */
final class SliderColumns {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'manage_' . SliderPostType::POST_TYPE . '_posts_columns', array( $this, 'add_columns' ) );
		add_action( 'manage_' . SliderPostType::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
	}

	/**
	 * Add custom columns.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function add_columns( array $columns ): array {
		$new_columns = array();

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;

			if ( 'title' === $key ) {
				$new_columns['lw_shortcode'] = __( 'Shortcode', 'lw-slider' );
				$new_columns['lw_slides']    = __( 'Slides', 'lw-slider' );
			}
		}

		unset( $new_columns['date'] );
		$new_columns['date'] = __( 'Date', 'lw-slider' );

		return $new_columns;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( string $column, int $post_id ): void {
		if ( 'lw_shortcode' === $column ) {
			$this->render_shortcode_column( $post_id );
		}

		if ( 'lw_slides' === $column ) {
			$this->render_slides_column( $post_id );
		}
	}

	/**
	 * Render the shortcode column.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function render_shortcode_column( int $post_id ): void {
		$shortcode = '[lw_slider id="' . $post_id . '"]';
		printf(
			'<code style="cursor:pointer;user-select:all;" title="%s">%s</code>',
			esc_attr__( 'Click to select', 'lw-slider' ),
			esc_html( $shortcode )
		);
	}

	/**
	 * Render the slides column.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function render_slides_column( int $post_id ): void {
		$slides = get_post_meta( $post_id, '_lw_slider_slides', true );

		if ( ! is_array( $slides ) || empty( $slides ) ) {
			echo '0';
			return;
		}

		$total  = count( $slides );
		$active = count( array_filter( $slides, fn( $s ) => ! empty( $s['active'] ) ) );

		printf(
			'<span title="%s">%s / %s</span>',
			/* translators: %1$d: active slides, %2$d: total slides */
			esc_attr( sprintf( __( '%1$d active of %2$d total', 'lw-slider' ), $active, $total ) ),
			esc_html( (string) $active ),
			esc_html( (string) $total )
		);
	}
}
