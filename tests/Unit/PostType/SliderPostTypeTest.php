<?php
/**
 * Tests for the slider post type registration.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\PostType;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\PostType\SliderPostType;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;

/**
 * The post type stays private: no front end and no core REST route
 * (/wp/v2/lw-slider listed slider titles without logging in).
 */
final class SliderPostTypeTest extends MonkeyTestCase {

	public function test_the_post_type_is_private_and_not_in_the_core_rest_api(): void {
		Functions\stubTranslationFunctions();
		$registered = [];
		Functions\when( 'register_post_type' )->alias(
			static function ( $post_type, $args ) use ( &$registered ) {
				$registered[ $post_type ] = $args;
			}
		);

		SliderPostType::register();

		$args = $registered['lw-slider'];
		$this->assertFalse( $args['public'] );
		$this->assertFalse( $args['publicly_queryable'] );
		$this->assertFalse( $args['show_in_rest'] );
		$this->assertTrue( $args['show_ui'] );
	}
}
