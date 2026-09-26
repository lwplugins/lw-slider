/**
 * Slide list helpers. Every slide in the draft carries a client-only `_key`
 * (React keys, focus after a move); it is stripped before sending.
 */
/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { DEFAULT_SLIDE } from './boot';

let counter = 0;
const nextKey = () => `s${ ++counter }`;

/**
 * Give every slide a key.
 *
 * @param {Object[]} slides Slides from the server.
 * @return {Object[]} Keyed slides.
 */
export const withKeys = ( slides ) =>
	( slides || [] ).map( ( slide ) => ( { ...slide, _key: nextKey() } ) );

/**
 * Drop the client-only keys.
 *
 * @param {Object[]} slides Keyed slides.
 * @return {Object[]} Slides to send.
 */
export const stripKeys = ( slides ) =>
	( slides || [] ).map( ( { _key, ...slide } ) => slide );

/**
 * A new slide: the defaults, active, and without an overlay (existing
 * slides keep whatever overlay they have).
 *
 * @return {Object} Keyed slide.
 */
export const newSlide = () => ( {
	...DEFAULT_SLIDE,
	active: true,
	overlay_color: '',
	_key: nextKey(),
} );

/**
 * A deep copy of a slide with a new key.
 *
 * @param {Object} slide Keyed slide.
 * @return {Object} Copy.
 */
export const copySlide = ( slide ) => ( {
	...JSON.parse( JSON.stringify( slide ) ),
	_key: nextKey(),
} );

/**
 * Move one item of a list.
 *
 * @param {Array}  list Items.
 * @param {number} from Index to move.
 * @param {number} to   Target index.
 * @return {Array} New list.
 */
export function moveItem( list, from, to ) {
	if ( from === to || to < 0 || to >= list.length ) {
		return list;
	}
	const next = [ ...list ];
	const [ item ] = next.splice( from, 1 );
	next.splice( to, 0, item );
	return next;
}

/**
 * Name of a slide in lists and messages: its headline, or "Slide 3".
 *
 * @param {Object} slide Slide.
 * @param {number} index Zero-based position.
 * @return {string} Name.
 */
export const slideName = ( slide, index ) =>
	slide.headline?.trim() ||
	sprintf(
		/* translators: %d: slide position (1, 2, …). */ __(
			'Slide %d',
			'lw-slider'
		),
		index + 1
	);

/**
 * Field errors of one slide, keyed by field.
 *
 * @param {Object} errors All field errors ({ 'slides.2.link_url': [...] }).
 * @param {number} index  Slide position.
 * @return {Object} { field: [ messages ] } (`_slide` for errors of the whole slide).
 */
export function slideErrors( errors, index ) {
	const prefix = `slides.${ index }`;
	const out = {};
	Object.entries( errors ).forEach( ( [ key, messages ] ) => {
		if ( key === prefix ) {
			out._slide = messages;
		} else if ( key.startsWith( prefix + '.' ) ) {
			out[ key.slice( prefix.length + 1 ) ] = messages;
		}
	} );
	return out;
}
