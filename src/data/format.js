/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * "5 minutes ago"-style age of a GMT MySQL datetime ("Y-m-d H:i:s").
 *
 * @param {string} gmt Datetime in UTC.
 * @return {string} Relative time, or '' when it cannot be read.
 */
export function timeAgo( gmt ) {
	const time = Date.parse( String( gmt ).replace( ' ', 'T' ) + 'Z' );
	if ( Number.isNaN( time ) ) {
		return '';
	}
	const minutes = Math.max( 0, Math.round( ( Date.now() - time ) / 60000 ) );
	if ( minutes < 1 ) {
		return __( 'just now', 'lw-slider' );
	}
	if ( minutes < 60 ) {
		return sprintf(
			/* translators: %d: number of minutes. */ _n(
				'%d minute ago',
				'%d minutes ago',
				minutes,
				'lw-slider'
			),
			minutes
		);
	}
	const hours = Math.round( minutes / 60 );
	if ( hours < 24 ) {
		return sprintf(
			/* translators: %d: number of hours. */ _n(
				'%d hour ago',
				'%d hours ago',
				hours,
				'lw-slider'
			),
			hours
		);
	}
	return new Date( time ).toLocaleDateString( undefined, {
		year: 'numeric',
		month: 'short',
		day: 'numeric',
	} );
}

/**
 * Full local date and time of a GMT MySQL datetime (for a title tooltip).
 *
 * @param {string} gmt Datetime in UTC.
 * @return {string} Local date and time.
 */
export function fullDate( gmt ) {
	const time = Date.parse( String( gmt ).replace( ' ', 'T' ) + 'Z' );
	return Number.isNaN( time ) ? '' : new Date( time ).toLocaleString();
}
