<?php
/**
 * Docs URL unit tests.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

namespace LightweightPlugins\Slider\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use LightweightPlugins\Slider\Admin\AppPage;
use LightweightPlugins\Slider\Tests\Unit\MonkeyTestCase;

final class DocsUrlTest extends MonkeyTestCase {

	/**
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function locale_provider(): array {
		return [
			'Hungarian'     => [ 'hu_HU', 'hu' ],
			'US English'    => [ 'en_US', 'en' ],
			'other locale'  => [ 'de_DE', 'en' ],
		];
	}

	/**
	 * @dataProvider locale_provider
	 */
	public function test_docs_url_follows_the_admin_user_locale( string $locale, string $lang ): void {
		Functions\when( 'get_user_locale' )->justReturn( $locale );

		$this->assertSame( 'https://docs.lwplugins.com/' . $lang . '/plugins/lw-slider', AppPage::docs_url() );
	}
}
