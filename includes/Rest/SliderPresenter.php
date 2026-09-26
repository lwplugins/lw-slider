<?php
/**
 * Shapes sliders for the admin REST responses.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Rest;

use LightweightPlugins\Slider\Data\SliderRepository;
use WP_Post;

/**
 * A list row and the full editor payload. Titles are raw text: the admin
 * renders them as text only, never as HTML.
 */
final class SliderPresenter {

	/**
	 * One row of the slider list.
	 *
	 * @param WP_Post $post Slider.
	 * @return array<string, mixed>
	 */
	public static function item( WP_Post $post ): array {
		$slides = SliderRepository::slides( $post->ID );
		$active = array_values( array_filter( $slides, static fn( array $slide ): bool => ! empty( $slide['active'] ) ) );

		return [
			'id'         => $post->ID,
			'title'      => $post->post_title,
			'status'     => $post->post_status,
			'modified'   => $post->post_modified_gmt,
			'slides'     => [
				'total'  => count( $slides ),
				'active' => count( $active ),
			],
			'thumb'      => self::thumb( $active ),
			'shortcode'  => self::shortcode( $post->ID ),
			'can_delete' => current_user_can( 'delete_post', $post->ID ),
		];
	}

	/**
	 * The editor payload: meta with defaults filled in, and the images the
	 * slides use.
	 *
	 * @param WP_Post $post Slider.
	 * @return array<string, mixed>
	 */
	public static function full( WP_Post $post ): array {
		$slides   = SliderRepository::slides( $post->ID );
		$settings = SliderRepository::settings( $post->ID );

		// The stored min heights are strings (1.0 shape); the admin gets numbers.
		$settings['min_height_desktop'] = (int) $settings['min_height_desktop'];
		$settings['min_height_mobile']  = (int) $settings['min_height_mobile'];

		return [
			'id'         => $post->ID,
			'title'      => $post->post_title,
			'status'     => $post->post_status,
			'modified'   => $post->post_modified_gmt,
			'shortcode'  => self::shortcode( $post->ID ),
			'can_delete' => current_user_can( 'delete_post', $post->ID ),
			'settings'   => $settings,
			'slides'     => $slides,
			'images'     => self::images( $slides ),
		];
	}

	/**
	 * Preview data of every image the slides use, keyed by attachment ID.
	 * A deleted image is flagged as missing.
	 *
	 * @param array<int, array<string, mixed>> $slides Slides.
	 * @return array<int, array<string, mixed>>
	 */
	public static function images( array $slides ): array {
		$ids = array_values( array_unique( array_filter( array_map( static fn( array $slide ): int => (int) $slide['bg_image_id'], $slides ) ) ) );

		if ( [] !== $ids && function_exists( '_prime_post_caches' ) ) {
			_prime_post_caches( $ids, false, true );
		}

		$images = [];

		foreach ( $ids as $id ) {
			$medium = wp_get_attachment_image_url( $id, 'medium' );

			$images[ $id ] = [
				'id'      => $id,
				'missing' => false === $medium,
				'thumb'   => (string) wp_get_attachment_image_url( $id, 'thumbnail' ),
				'medium'  => (string) $medium,
				'alt'     => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
			];
		}

		return $images;
	}

	/**
	 * The shortcode of a slider.
	 *
	 * @param int $id Slider ID.
	 * @return string
	 */
	public static function shortcode( int $id ): string {
		return '[lw_slider id="' . $id . '"]';
	}

	/**
	 * Thumbnail of the first active image slide, or null.
	 *
	 * @param array<int, array<string, mixed>> $active Active slides.
	 * @return string|null
	 */
	private static function thumb( array $active ): ?string {
		foreach ( $active as $slide ) {
			if ( 'image' === $slide['bg_type'] && (int) $slide['bg_image_id'] > 0 ) {
				$url = wp_get_attachment_image_url( (int) $slide['bg_image_id'], 'thumbnail' );
				return $url ? $url : null;
			}
		}

		return null;
	}
}
