<?php
/**
 * Sends the classic slider screens to the slider manager.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Admin;

use LightweightPlugins\Slider\PostType\SliderPostType;

/**
 * The post type keeps its core screens (show_ui) so capabilities, links
 * and bookmarks keep working, but plain page loads (GET, no list action) of
 * the list, "Add New" and "Edit" land in the app instead, on the same
 * slider. Edit links point straight at the app.
 */
final class ClassicRedirect {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'load-edit.php', [ $this, 'from_list' ] );
		add_action( 'load-post-new.php', [ $this, 'from_new' ] );
		add_action( 'load-post.php', [ $this, 'from_edit' ] );
		add_filter( 'get_edit_post_link', [ $this, 'edit_link' ], 10, 2 );
	}

	/**
	 * Where the list (edit.php?post_type=lw-slider) goes, or null to stay.
	 * List actions (bulk trash, untrash, delete) run as usual.
	 *
	 * @param array<string, mixed> $query  Query args.
	 * @param string               $method HTTP method.
	 * @return string|null App route.
	 */
	public static function list_target( array $query, string $method ): ?string {
		if ( 'GET' !== $method || SliderPostType::POST_TYPE !== ( $query['post_type'] ?? '' ) ) {
			return null;
		}

		foreach ( [ 'action', 'action2' ] as $key ) {
			if ( isset( $query[ $key ] ) && '' !== $query[ $key ] && '-1' !== (string) $query[ $key ] ) {
				return null;
			}
		}

		return 'trash' === ( $query['post_status'] ?? '' ) ? 'trash' : 'sliders';
	}

	/**
	 * Where "Edit" (post.php?action=edit&post=N) goes, or null to stay.
	 *
	 * @param array<string, mixed> $query     Query args.
	 * @param string               $method    HTTP method.
	 * @param string               $post_type Post type of the post.
	 * @return string|null App route.
	 */
	public static function edit_target( array $query, string $method, string $post_type ): ?string {
		if ( 'GET' !== $method || 'edit' !== ( $query['action'] ?? '' ) || SliderPostType::POST_TYPE !== $post_type ) {
			return null;
		}

		return 'slider/' . absint( $query['post'] ?? 0 );
	}

	/**
	 * Redirect the list.
	 *
	 * @return void
	 */
	public function from_list(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing of a page load.
		$this->go( self::list_target( wp_unslash( $_GET ), self::method() ) );
	}

	/**
	 * Redirect "Add New".
	 *
	 * @return void
	 */
	public function from_new(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing of a page load.
		$type = sanitize_key( wp_unslash( $_GET['post_type'] ?? '' ) );

		$this->go( 'GET' === self::method() && SliderPostType::POST_TYPE === $type ? 'new' : null );
	}

	/**
	 * Redirect "Edit".
	 *
	 * @return void
	 */
	public function from_edit(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing of a page load.
		$query = wp_unslash( $_GET );
		$type  = (string) get_post_type( absint( $query['post'] ?? 0 ) );

		$this->go( self::edit_target( $query, self::method(), $type ) );
	}

	/**
	 * Edit links of sliders point at the app.
	 *
	 * @param string|null $link    Core edit link.
	 * @param int         $post_id Post ID.
	 * @return string|null
	 */
	public function edit_link( $link, $post_id ) {
		if ( SliderPostType::POST_TYPE !== get_post_type( (int) $post_id ) || empty( $link ) ) {
			return $link;
		}

		return AppPage::url( 'slider/' . (int) $post_id );
	}

	/**
	 * Current HTTP method.
	 *
	 * @return string
	 */
	private static function method(): string {
		return strtoupper( sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) );
	}

	/**
	 * Redirect to an app route and stop.
	 *
	 * @param string|null $route App route, or null to stay.
	 * @return void
	 */
	private function go( ?string $route ): void {
		if ( null === $route ) {
			return;
		}

		wp_safe_redirect( AppPage::url( $route ) );
		exit;
	}
}
