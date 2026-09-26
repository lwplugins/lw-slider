<?php
/**
 * Behavioural stand-ins for the WordPress helpers the plugin calls.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit;

use Brain\Monkey\Functions;

/**
 * Simplified but faithful copies of the core sanitizers, slashing helpers
 * and a post meta store. Tests assert on what WordPress would really store.
 */
trait StubsWordPress {

	/**
	 * Post meta written through update_post_meta(): [ post_id ][ key ] => value
	 * (unslashed, like core stores it).
	 *
	 * @var array<int, array<string, mixed>>
	 */
	protected array $meta = [];

	/**
	 * Stub the sanitizers, slashing helpers and escaping functions.
	 *
	 * @return void
	 */
	protected function stub_wordpress(): void {
		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();

		$deep = static function ( $value, callable $fn ) use ( &$deep ) {
			if ( is_array( $value ) ) {
				return array_map( static fn( $item ) => $deep( $item, $fn ), $value );
			}
			return is_string( $value ) ? $fn( $value ) : $value;
		};

		Functions\stubs(
			[
				'absint'                  => static fn( $value ) => abs( (int) $value ),
				'sanitize_key'            => static fn( $key ) => preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ),
				'sanitize_text_field'     => static function ( $value ) {
					if ( is_array( $value ) || is_object( $value ) ) {
						return '';
					}
					$value = strip_tags( (string) $value );
					$value = preg_replace( '/[\r\n\t ]+/', ' ', $value );
					return trim( (string) preg_replace( '/%[a-f0-9]{2}/i', '', (string) $value ) );
				},
				'sanitize_textarea_field' => static fn( $value ) => ( is_array( $value ) || is_object( $value ) ) ? '' : trim( strip_tags( (string) $value ) ),
				'sanitize_hex_color'      => static function ( $color ) {
					if ( '' === $color ) {
						return '';
					}
					return is_string( $color ) && preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ? $color : null;
				},
				'sanitize_html_class'     => static function ( $classname ) {
					$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', (string) $classname );
					return (string) preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $sanitized );
				},
				'esc_url_raw'             => static function ( $url ) {
					$url = trim( (string) $url );
					if ( '' === $url ) {
						return '';
					}
					if ( '/' === $url[0] || '#' === $url[0] ) {
						return $url;
					}
					return preg_match( '#^(https?:|mailto:|tel:)#i', $url ) ? str_replace( ' ', '%20', $url ) : ( preg_match( '#^[a-z][a-z0-9+.-]*:#i', $url ) ? '' : 'http://' . $url );
				},
				'wp_unslash'              => static fn( $value ) => $deep( $value, 'stripslashes' ),
				'wp_slash'                => static fn( $value ) => $deep( $value, 'addslashes' ),
				'wp_parse_args'           => static fn( $args, $defaults = [] ) => array_merge( (array) $defaults, (array) $args ),
				'wp_json_encode'          => static fn( $data ) => json_encode( $data ),
			]
		);
	}

	/**
	 * A post meta store: update_post_meta() unslashes like core does,
	 * get_post_meta() reads it back.
	 *
	 * @return void
	 */
	protected function stub_meta_store(): void {
		Functions\when( 'update_post_meta' )->alias(
			function ( $post_id, $key, $value ) {
				$this->meta[ (int) $post_id ][ $key ] = wp_unslash( $value );
				return true;
			}
		);

		Functions\when( 'get_post_meta' )->alias(
			fn( $post_id, $key = '', $single = false ) => $this->meta[ (int) $post_id ][ $key ] ?? ( $single ? '' : [] )
		);
	}
}
