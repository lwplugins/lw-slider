<?php
/**
 * Validates a slider create/update request body.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Rest\Input;

use LightweightPlugins\Slider\Rest\SliderPermissions;
use WP_Post;

/**
 * The whole body is checked before anything is written; any error means
 * nothing is saved. Keys: title, status, settings (partial), slides (full
 * ordered list), modified (the concurrency token, checked by the
 * controller).
 */
final class SliderInput {

	/**
	 * Statuses the admin sets.
	 */
	public const STATUSES = [ 'draft', 'publish' ];

	/**
	 * Longest title.
	 */
	private const TITLE_MAX = 200;

	/**
	 * Field errors: path => messages.
	 *
	 * @var array<string, array<int, string>>
	 */
	private array $errors = [];

	/**
	 * Accepted values by top-level key.
	 *
	 * @var array<string, mixed>
	 */
	private array $values = [];

	/**
	 * Validate a body.
	 *
	 * @param array<array-key, mixed> $body    Decoded JSON body.
	 * @param WP_Post|null            $current Slider being updated (null on create).
	 */
	public function __construct( array $body, ?WP_Post $current = null ) {
		foreach ( $body as $key => $value ) {
			switch ( (string) $key ) {
				case 'title':
					$this->title( $value );
					break;
				case 'status':
					$this->status( $value, $current );
					break;
				case 'settings':
					$this->values['settings'] = SettingsValidator::validate( $value, $this->errors );
					break;
				case 'slides':
					$this->values['slides'] = SlideValidator::validate( $value, $this->errors );
					break;
				case 'modified':
					$this->values['modified'] = is_string( $value ) ? $value : '';
					break;
				default:
					$this->errors[ (string) $key ][] = __( 'Unknown field.', 'lw-slider' );
			}
		}
	}

	/**
	 * Field errors (empty when the body is valid).
	 *
	 * @return array<string, array<int, string>>
	 */
	public function errors(): array {
		return $this->errors;
	}

	/**
	 * Whether a top-level key was sent and accepted.
	 *
	 * @param string $key Key.
	 * @return bool
	 */
	public function has( string $key ): bool {
		return array_key_exists( $key, $this->values );
	}

	/**
	 * An accepted value.
	 *
	 * @param string $key Key.
	 * @return mixed
	 */
	public function get( string $key ) {
		return $this->values[ $key ] ?? null;
	}

	/**
	 * Title: text, tags stripped.
	 *
	 * @param mixed $value Submitted value.
	 * @return void
	 */
	private function title( $value ): void {
		$error = Rules::text( $value, self::TITLE_MAX, $clean );

		if ( null !== $error ) {
			$this->errors['title'][] = $error;
			return;
		}

		$this->values['title'] = sanitize_text_field( $clean );
	}

	/**
	 * Status: draft or publish (publishing needs publish_posts). A status
	 * the admin does not set (pending, private, future) may be sent back
	 * unchanged.
	 *
	 * @param mixed        $value   Submitted value.
	 * @param WP_Post|null $current Current slider.
	 * @return void
	 */
	private function status( $value, ?WP_Post $current ): void {
		$unchanged = null !== $current && $value === $current->post_status;

		if ( $unchanged ) {
			$this->values['status'] = $value;
			return;
		}

		$error = Rules::choice( $value, self::STATUSES, $clean );

		if ( null === $error && 'publish' === $clean && ! SliderPermissions::can_publish() ) {
			$error = __( 'You are not allowed to publish sliders.', 'lw-slider' );
		}

		if ( null !== $error ) {
			$this->errors['status'][] = $error;
			return;
		}

		$this->values['status'] = $clean;
	}
}
