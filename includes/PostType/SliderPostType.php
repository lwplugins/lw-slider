<?php
/**
 * Slider custom post type.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\PostType;

use LightweightPlugins\Slider\Admin\ParentPage;

/**
 * Registers the Slider custom post type.
 */
final class SliderPostType {

	/**
	 * Post type slug.
	 */
	public const POST_TYPE = 'lw-slider';

	/**
	 * Register the post type on init.
	 *
	 * @return void
	 */
	public static function register(): void {
		$labels = array(
			'name'                  => _x( 'Sliders', 'Post type general name', 'lw-slider' ),
			'singular_name'         => _x( 'Slider', 'Post type singular name', 'lw-slider' ),
			'menu_name'             => _x( 'Sliders', 'Admin menu text', 'lw-slider' ),
			'add_new'               => __( 'Add New', 'lw-slider' ),
			'add_new_item'          => __( 'Add New Slider', 'lw-slider' ),
			'edit_item'             => __( 'Edit Slider', 'lw-slider' ),
			'new_item'              => __( 'New Slider', 'lw-slider' ),
			'view_item'             => __( 'View Slider', 'lw-slider' ),
			'view_items'            => __( 'View Sliders', 'lw-slider' ),
			'search_items'          => __( 'Search Sliders', 'lw-slider' ),
			'not_found'             => __( 'No sliders found', 'lw-slider' ),
			'not_found_in_trash'    => __( 'No sliders found in Trash', 'lw-slider' ),
			'all_items'             => __( 'Sliders', 'lw-slider' ),
			'archives'              => __( 'Slider Archives', 'lw-slider' ),
			'insert_into_item'      => __( 'Insert into slider', 'lw-slider' ),
			'uploaded_to_this_item' => __( 'Uploaded to this slider', 'lw-slider' ),
			'filter_items_list'     => __( 'Filter sliders list', 'lw-slider' ),
			'items_list_navigation' => __( 'Sliders list navigation', 'lw-slider' ),
			'items_list'            => __( 'Sliders list', 'lw-slider' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => ParentPage::SLUG,
			'show_in_rest'       => true,
			'query_var'          => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
