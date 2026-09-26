/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { parseRoute } from '../data/route';

/**
 * Current route from location.hash. `shouldBlock( next )` may hold a
 * navigation back (e.g. leaving a slider with unsaved changes): the hash is
 * put back and `onBlocked( nextHash )` decides what happens next.
 *
 * @param {Object}                    options
 * @param {(next: Object) => boolean} options.shouldBlock Block this move?
 * @param {(hash: string) => void}    options.onBlocked   Called with the blocked hash.
 * @return {Object} { route, go( hash ) } — go() skips the guard.
 */
export default function useRoute( { shouldBlock, onBlocked } ) {
	const [ route, setRoute ] = useState( () =>
		parseRoute( window.location.hash )
	);
	const current = useRef( window.location.hash );
	const guard = useRef( { shouldBlock, onBlocked } );
	const skip = useRef( false );

	useEffect( () => {
		guard.current = { shouldBlock, onBlocked };
	} );

	useEffect( () => {
		const onChange = () => {
			const hash = window.location.hash;
			const next = parseRoute( hash );
			if ( ! skip.current && guard.current.shouldBlock( next ) ) {
				window.history.replaceState(
					null,
					'',
					current.current || '#sliders'
				);
				guard.current.onBlocked( hash );
				return;
			}
			skip.current = false;
			current.current = hash;
			setRoute( next );
			window.scrollTo( { top: 0 } );
		};
		window.addEventListener( 'hashchange', onChange );
		return () => window.removeEventListener( 'hashchange', onChange );
	}, [] );

	const go = ( hash ) => {
		if ( hash === window.location.hash ) {
			setRoute( parseRoute( hash ) );
			return;
		}
		skip.current = true;
		window.location.hash = hash;
	};

	return { route, go };
}
