/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Post status labels and their badge tone.
 *
 * @return {Object} { status: { label, tone } }.
 */
export const statuses = () => ( {
	publish: { label: __( 'Published', 'lw-slider' ), tone: 'ok' },
	draft: { label: __( 'Draft', 'lw-slider' ), tone: 'idle' },
	pending: { label: __( 'Pending review', 'lw-slider' ), tone: 'warning' },
	private: { label: __( 'Private', 'lw-slider' ), tone: 'info' },
	future: { label: __( 'Scheduled', 'lw-slider' ), tone: 'info' },
	trash: { label: __( 'In the trash', 'lw-slider' ), tone: 'critical' },
} );

/**
 * Label of a status (the raw value for an unknown one).
 *
 * @param {string} status Post status.
 * @return {string} Label.
 */
export const statusLabel = ( status ) => statuses()[ status ]?.label || status;
