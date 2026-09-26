/**
 * Every REST call the admin makes, in one place (lw-slider/v1, prefix /admin).
 */
/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { NAMESPACE } from './boot';

const path = ( route ) => `/${ NAMESPACE }/admin${ route }`;

/**
 * A slider with its title as plain text. Core stores "&" as "&amp;" for
 * users without unfiltered_html; the admin shows and edits the text.
 *
 * @param {Object} slider Slider (or another response).
 * @return {Object} Same object with a decoded title.
 */
const decoded = ( slider ) =>
	typeof slider?.title === 'string'
		? { ...slider, title: decodeEntities( slider.title ) }
		: slider;

const get = ( route ) => apiFetch( { path: path( route ) } );
const send = ( route, method, data ) =>
	apiFetch( { path: path( route ), method, data } ).then( decoded );

export const api = {
	// GET → { items, meta }.
	list: () =>
		get( '/sliders' ).then( ( res ) => ( {
			...res,
			items: ( res?.items || [] ).map( decoded ),
		} ) ),
	// POST { title, status?, settings?, slides? } → slider (201).
	create: ( body ) => send( '/sliders', 'POST', body ),
	get: ( id ) => get( `/sliders/${ id }` ).then( decoded ),
	// Partial, atomic; `modified` guards against overwriting newer changes.
	update: ( id, patch ) => send( `/sliders/${ id }`, 'POST', patch ),
	trash: ( id ) => send( `/sliders/${ id }`, 'DELETE' ),
	remove: ( id ) => send( `/sliders/${ id }?force=true`, 'DELETE' ),
	restore: ( id ) => send( `/sliders/${ id }/restore`, 'POST' ),
	duplicate: ( id ) => send( `/sliders/${ id }/duplicate`, 'POST' ),
};

/**
 * Human message of a failed request.
 *
 * @param {Object} error apiFetch rejection.
 * @return {string} Message.
 */
export const errorMessage = ( error ) =>
	error?.message ||
	__( 'That did not work. Reload the page and try again.', 'lw-slider' );

/**
 * Per-field validation errors of a `400 lw_slider_invalid` response.
 *
 * @param {Object} error apiFetch rejection.
 * @return {Object|null} { path: [ messages ] } or null.
 */
export function fieldErrors( error ) {
	const fields = error?.data?.fields;
	if ( ! fields || typeof fields !== 'object' ) {
		return null;
	}
	return Object.fromEntries(
		Object.entries( fields ).map( ( [ key, messages ] ) => [
			key,
			( Array.isArray( messages ) ? messages : [ messages ] ).map(
				String
			),
		] )
	);
}
