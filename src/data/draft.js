/**
 * Pure helpers of the editor draft: what changed, and how to rebase the live
 * draft onto a save response.
 */
/**
 * Internal dependencies
 */
import { stripKeys, withKeys } from './slides';

export const same = ( a, b ) => JSON.stringify( a ) === JSON.stringify( b );

/**
 * The editable part of a server slider.
 *
 * @param {Object} slider Server slider.
 * @return {Object} { title, status, settings, slides (keyed) }.
 */
export const draftOf = ( slider ) => ( {
	title: slider.title,
	status: slider.status,
	settings: { ...slider.settings },
	slides: withKeys( slider.slides ),
} );

/**
 * The update body: only what differs from the server copy. Settings are
 * sent per key (merged on the server), slides as the whole ordered list.
 *
 * @param {Object} server Server slider.
 * @param {Object} draft  Draft.
 * @return {Object} Patch (empty when nothing changed).
 */
export function patchOf( server, draft ) {
	const patch = {};

	if ( draft.title !== server.title ) {
		patch.title = draft.title;
	}
	if ( draft.status !== server.status ) {
		patch.status = draft.status;
	}

	const settings = {};
	Object.keys( draft.settings ).forEach( ( key ) => {
		if ( ! same( draft.settings[ key ], server.settings[ key ] ) ) {
			settings[ key ] = draft.settings[ key ];
		}
	} );
	if ( Object.keys( settings ).length ) {
		patch.settings = settings;
	}

	const slides = stripKeys( draft.slides );
	if ( ! same( slides, server.slides ) ) {
		patch.slides = slides;
	}

	return patch;
}

/**
 * Rebase the live draft onto a save response: start from what the server
 * saved and keep every part the user changed after the save was sent, so
 * edits made mid-save are not lost. Slides that did not change keep their
 * keys (focus and selection survive).
 *
 * @param {Object} saved Server slider after the save.
 * @param {Object} sent  Draft snapshot the save was built from.
 * @param {Object} live  Current draft.
 * @return {Object} Rebased draft.
 */
export function rebaseDraft( saved, sent, live ) {
	const next = draftOf( saved );

	if ( live.title !== sent.title ) {
		next.title = live.title;
	}
	if ( live.status !== sent.status ) {
		next.status = live.status;
	}
	Object.keys( live.settings ).forEach( ( key ) => {
		if ( ! same( live.settings[ key ], sent.settings[ key ] ) ) {
			next.settings[ key ] = live.settings[ key ];
		}
	} );

	if ( ! same( stripKeys( live.slides ), stripKeys( sent.slides ) ) ) {
		next.slides = live.slides;
	} else {
		next.slides = next.slides.map( ( slide, index ) => ( {
			...slide,
			_key: live.slides[ index ]?._key || slide._key,
		} ) );
	}

	return next;
}
