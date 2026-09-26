<?php
/**
 * PHPUnit bootstrap file.
 *
 * Unit tests run WITHOUT WordPress: only the Composer autoloader is loaded,
 * which also pulls in Brain Monkey. WordPress functions are stubbed per test
 * via Brain\Monkey; the setUp()/tearDown() lifecycle lives in
 * tests/Unit/MonkeyTestCase.php.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'LW_SLIDER_VERSION' ) ) {
	define( 'LW_SLIDER_VERSION', '0.0.0-test' );
	define( 'LW_SLIDER_FILE', dirname( __DIR__ ) . '/lw-slider.php' );
	define( 'LW_SLIDER_PATH', dirname( __DIR__ ) . '/' );
	define( 'LW_SLIDER_URL', 'https://example.test/wp-content/plugins/lw-slider/' );
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
require_once __DIR__ . '/Fixtures/WordPressClasses.php';
