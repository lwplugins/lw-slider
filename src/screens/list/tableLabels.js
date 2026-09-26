/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Translated UI strings for `@lwplugins/data-table` (it has no text domain).
 *
 * @param {boolean} trash Labels of the Trash view.
 * @return {Object} Labels.
 */
export function tableLabels( trash ) {
	return {
		search: __( 'Search sliders', 'lw-slider' ),
		filter: __( 'Filter', 'lw-slider' ),
		clear: __( 'Clear', 'lw-slider' ),
		clearAll: __( 'Clear all filters', 'lw-slider' ),
		all: __( 'All', 'lw-slider' ),
		empty: __( 'No sliders match your search.', 'lw-slider' ),
		emptyAll: trash
			? __( 'The trash is empty.', 'lw-slider' )
			: __( 'No sliders yet.', 'lw-slider' ),
		loading: __( 'Loading…', 'lw-slider' ),
		previous: __( 'Previous page', 'lw-slider' ),
		next: __( 'Next page', 'lw-slider' ),
		perPage: __( 'Rows per page', 'lw-slider' ),
		selectAll: __( 'Select all sliders on this page', 'lw-slider' ),
		clearSelection: __( 'Clear selection', 'lw-slider' ),
		bulkActions: __( 'Bulk actions', 'lw-slider' ),
		entries: ( n ) =>
			sprintf(
				/* translators: %d: number of sliders. */ _n(
					'%d slider',
					'%d sliders',
					n,
					'lw-slider'
				),
				n
			),
		results: ( n ) =>
			sprintf(
				/* translators: %d: number of results. */ _n(
					'%d result',
					'%d results',
					n,
					'lw-slider'
				),
				n
			),
		page: ( p, t ) =>
			sprintf(
				/* translators: 1: current page, 2: total pages. */ __(
					'Page %1$d of %2$d',
					'lw-slider'
				),
				p,
				t
			),
		selectRow: ( label ) =>
			sprintf(
				/* translators: %s: slider name. */ __(
					'Select: %s',
					'lw-slider'
				),
				label
			),
		selected: ( n ) =>
			sprintf(
				/* translators: %d: number of selected sliders. */ _n(
					'%d selected',
					'%d selected',
					n,
					'lw-slider'
				),
				n
			),
		eligible: ( e, n ) =>
			sprintf(
				/* translators: 1: sliders the action applies to, 2: selected sliders. */ __(
					'applies to %1$d of %2$d',
					'lw-slider'
				),
				e,
				n
			),
	};
}
