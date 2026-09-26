<?php
/**
 * Admin REST routes bootstrap.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Rest;

use WP_Error;
use WP_REST_Request;

/**
 * Registers the lw-slider/v1/admin/* routes the React admin uses, and the
 * helpers they share. The block's GET lw-slider/v1/sliders is registered by
 * the block and is not touched by any of these.
 *
 * REST cookie auth supplies the nonce (X-WP-Nonce), so write routes need no
 * nonce of their own; every route checks a capability. Sliders use the core
 * post capabilities, per slider (edit_post, delete_post), as the classic
 * screens did.
 */
final class AdminRoutes {

	/**
	 * Namespace.
	 */
	public const NAMESPACE = 'lw-slider/v1';

	/**
	 * Largest accepted request body, in bytes.
	 */
	public const MAX_BYTES = 1048576;

	/**
	 * Register every admin route (on rest_api_init).
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		( new SlidersController() )->register_routes();
		( new SliderActionsController() )->register_routes();
	}

	/**
	 * A REST error.
	 *
	 * @param string               $code    Error code.
	 * @param string               $message Translated message.
	 * @param int                  $status  HTTP status.
	 * @param array<string, mixed> $data    Extra error data.
	 * @return WP_Error
	 */
	public static function error( string $code, string $message, int $status, array $data = [] ): WP_Error {
		return new WP_Error( $code, $message, array_merge( [ 'status' => $status ], $data ) );
	}

	/**
	 * The "some fields are not valid" error (nothing was saved).
	 *
	 * @param array<string, array<int, string>> $fields Field path => messages.
	 * @return WP_Error
	 */
	public static function invalid( array $fields ): WP_Error {
		return self::error(
			'lw_slider_invalid',
			__( 'Some fields are not valid. Nothing was saved.', 'lw-slider' ),
			400,
			[ 'fields' => $fields ]
		);
	}

	/**
	 * The "not found" error.
	 *
	 * @return WP_Error
	 */
	public static function not_found(): WP_Error {
		return self::error( 'lw_slider_not_found', __( 'That slider no longer exists.', 'lw-slider' ), 404 );
	}

	/**
	 * The "request too large" error, or null when the body fits.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_Error|null
	 */
	public static function too_large( WP_REST_Request $request ): ?WP_Error {
		if ( strlen( (string) $request->get_body() ) <= self::MAX_BYTES ) {
			return null;
		}

		return self::error( 'lw_slider_too_large', __( 'The request is too large.', 'lw-slider' ), 413 );
	}

	/**
	 * Decoded JSON body as an array (empty for an empty or invalid body).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return array<array-key, mixed>
	 */
	public static function body( WP_REST_Request $request ): array {
		// Null for an empty or undecodable body at runtime, despite the stub.
		$body = $request->get_json_params();

		return empty( $body ) ? [] : $body;
	}
}
