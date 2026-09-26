/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { store as noticesStore } from '@wordpress/notices';

/**
 * Internal dependencies
 */
import { api, errorMessage, fieldErrors } from './api';
import { draftOf, patchOf, rebaseDraft } from './draft';

/**
 * Drop the errors whose path starts with one of the prefixes.
 *
 * @param {Object}   errors   Field errors.
 * @param {string[]} prefixes Paths (exact or prefix + '.').
 * @return {Object} Remaining errors (same object when nothing was dropped).
 */
const without = ( errors, prefixes ) => {
	const keys = Object.keys( errors ).filter( ( key ) =>
		prefixes.some( ( p ) => key === p || key.startsWith( p + '.' ) )
	);
	if ( ! keys.length ) {
		return errors;
	}
	const next = { ...errors };
	keys.forEach( ( key ) => delete next[ key ] );
	return next;
};

/**
 * One slider being edited: the server copy, the draft, images, field
 * errors. One Save (top bar or Cmd/Ctrl+S) sends only what changed, with the
 * `modified` token; a 409 means the slider changed elsewhere.
 *
 * @param {number|null} id Slider ID (null: nothing is loaded).
 * @return {Object} Store.
 */
export default function useSliderStore( id ) {
	const [ server, setServer ] = useState( null );
	const [ draft, setDraft ] = useState( null );
	const [ images, setImages ] = useState( {} );
	const [ error, setError ] = useState( null );
	const [ errors, setErrors ] = useState( {} );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ conflict, setConflict ] = useState( false );
	const latest = useRef( 0 );
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	const apply = useCallback( ( data ) => {
		setServer( data );
		setDraft( draftOf( data ) );
		setImages( { ...( data.images || {} ) } );
		setErrors( {} );
		setConflict( false );
	}, [] );

	const reload = useCallback( () => {
		const ticket = ++latest.current;
		setError( null );
		setServer( null );
		setDraft( null );
		if ( ! id ) {
			return Promise.resolve();
		}
		return api.get( id ).then(
			( data ) => ticket === latest.current && apply( data ),
			( e ) => ticket === latest.current && setError( errorMessage( e ) )
		);
	}, [ id, apply ] );

	useEffect( () => {
		reload();
	}, [ reload ] );

	const patch = server && draft ? patchOf( server, draft ) : {};
	const hasEdits = Object.keys( patch ).length > 0;

	const update = ( fn, clear = [] ) => {
		setDraft( ( prev ) => fn( prev ) );
		if ( clear.length ) {
			setErrors( ( prev ) => without( prev, clear ) );
		}
	};

	const save = async ( overrides = {} ) => {
		const sent = { ...draft, ...overrides };
		const body = patchOf( server, sent );
		if ( isSaving || ! Object.keys( body ).length ) {
			return false;
		}
		if ( Object.keys( overrides ).length ) {
			setDraft( ( live ) => ( { ...live, ...overrides } ) );
		}
		setIsSaving( true );
		let ok = false;
		try {
			const data = await api.update( id, {
				...body,
				modified: server.modified,
			} );
			setServer( data );
			setDraft( ( live ) =>
				rebaseDraft( data, sent, { ...live, ...overrides } )
			);
			setImages( ( prev ) => ( { ...prev, ...( data.images || {} ) } ) );
			setErrors( {} );
			setConflict( false );
			ok = true;
			createSuccessNotice( __( 'Slider saved.', 'lw-slider' ), {
				type: 'snackbar',
			} );
		} catch ( e ) {
			const fields = fieldErrors( e );
			if ( fields ) {
				setErrors( fields );
			}
			if ( e?.code === 'lw_slider_conflict' ) {
				setConflict( true );
			}
			createErrorNotice(
				fields
					? __(
							'Nothing was saved. Fix the highlighted fields and save again.',
							'lw-slider'
						)
					: errorMessage( e ),
				{ type: 'snackbar' }
			);
		}
		setIsSaving( false );
		return ok;
	};

	return {
		id,
		server,
		draft,
		images,
		error,
		errors,
		conflict,
		isLoading: !! id && ! draft && ! error,
		isSaving,
		hasEdits,
		reload,
		save,
		discard: () => {
			setDraft( draftOf( server ) );
			setErrors( {} );
		},
		setTitle: ( title ) =>
			update( ( d ) => ( { ...d, title } ), [ 'title' ] ),
		setStatus: ( status ) =>
			update( ( d ) => ( { ...d, status } ), [ 'status' ] ),
		setSetting: ( key, value ) =>
			update(
				( d ) => ( {
					...d,
					settings: { ...d.settings, [ key ]: value },
				} ),
				[ `settings.${ key }`, 'settings' ]
			),
		// Edit one slide's fields; clears those fields' errors.
		setSlide: ( index, values ) =>
			update(
				( d ) => ( {
					...d,
					slides: d.slides.map( ( slide, i ) =>
						i === index ? { ...slide, ...values } : slide
					),
				} ),
				Object.keys( values ).map(
					( key ) => `slides.${ index }.${ key }`
				)
			),
		// Replace the list (add, remove, move): index-based errors no longer
		// point at the right slide, so they are cleared.
		setSlides: ( fn ) =>
			update(
				( d ) => ( { ...d, slides: fn( d.slides ) } ),
				[ 'slides' ]
			),
		addImage: ( image ) =>
			image &&
			setImages( ( prev ) => ( { ...prev, [ image.id ]: image } ) ),
	};
}
