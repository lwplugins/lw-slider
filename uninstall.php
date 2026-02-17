<?php
/**
 * Uninstall script.
 *
 * @package LightweightPlugins\Slider
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all slider posts and their meta.
$lw_slider_posts = get_posts(
	array(
		'post_type'      => 'lw-slider',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	)
);

foreach ( $lw_slider_posts as $lw_slider_post_id ) {
	wp_delete_post( $lw_slider_post_id, true );
}
