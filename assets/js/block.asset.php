<?php
/**
 * Dependencies and version of the block editor script.
 *
 * @package LightweightPlugins\Slider
 */

declare(strict_types=1);

return [
	'dependencies' => [
		'wp-api-fetch',
		'wp-block-editor',
		'wp-blocks',
		'wp-components',
		'wp-element',
		'wp-html-entities',
		'wp-i18n',
	],
	// The plugin version, so browsers fetch the script again after an update.
	'version'      => defined( 'LW_SLIDER_VERSION' ) ? LW_SLIDER_VERSION : '1',
];
