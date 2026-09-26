/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';

/**
 * Error wiring of one field: the message list id, and the aria attributes of
 * the control (only while there are messages).
 *
 * @param {Object}   Component Field component (instance id namespace).
 * @param {string[]} errors    Messages.
 * @return {Object} { errorId, describedBy, invalid }.
 */
export function useErrorIds( Component, errors = [] ) {
	const errorId = `${ useInstanceId( Component, 'lw-slider-field' ) }-errors`;
	const has = errors.length > 0;

	return {
		errorId,
		describedBy: has ? errorId : undefined,
		invalid: has || undefined,
	};
}
