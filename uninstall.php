<?php
/**
 * Uninstall script: deletes every slider (trashed ones too) and its meta,
 * on every site of a multisite network.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete the sliders of the current site. 'any' skips trash and
 * auto-draft, so every registered status is listed.
 *
 * @return void
 */
function lw_slider_uninstall_site(): void {
	$lw_slider_posts = get_posts(
		[
			'post_type'        => 'lw-slider',
			'posts_per_page'   => -1,
			'post_status'      => array_keys( get_post_stati() ),
			'fields'           => 'ids',
			'suppress_filters' => true,
		]
	);

	foreach ( $lw_slider_posts as $lw_slider_post_id ) {
		wp_delete_post( (int) $lw_slider_post_id, true );
	}
}

if ( is_multisite() ) {
	foreach ( get_sites(
		[
			'fields' => 'ids',
			'number' => 0,
		]
	) as $lw_slider_site_id ) {
		switch_to_blog( (int) $lw_slider_site_id );
		lw_slider_uninstall_site();
		restore_current_blog();
	}
} else {
	lw_slider_uninstall_site();
}
