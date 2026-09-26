/**
 * Hash routes of the app:
 * - `#sliders` (default) and `#trash`: the list views;
 * - `#new`: the list with the create dialog open;
 * - `#slider/12`, `#slider/12/settings`, `#slider/12/embed`: the editor.
 */
export const EDITOR_TABS = [ 'slides', 'settings', 'embed' ];

/**
 * Parse a location hash.
 *
 * @param {string} hash location.hash.
 * @return {Object} { view, id?, tab? }.
 */
export function parseRoute( hash ) {
	const parts = String( hash || '' )
		.replace( /^#\/?/, '' )
		.split( '/' )
		.filter( Boolean );

	if ( parts[ 0 ] === 'slider' && /^\d+$/.test( parts[ 1 ] || '' ) ) {
		const tab = EDITOR_TABS.includes( parts[ 2 ] ) ? parts[ 2 ] : 'slides';
		return { view: 'editor', id: Number( parts[ 1 ] ), tab };
	}

	if ( parts[ 0 ] === 'trash' || parts[ 0 ] === 'new' ) {
		return { view: parts[ 0 ] };
	}

	return { view: 'sliders' };
}

/**
 * Hash of an editor tab.
 *
 * @param {number} id  Slider ID.
 * @param {string} tab Tab.
 * @return {string} Hash (with #).
 */
export const editorHash = ( id, tab = 'slides' ) =>
	tab === 'slides' ? `#slider/${ id }` : `#slider/${ id }/${ tab }`;
