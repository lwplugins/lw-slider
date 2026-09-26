/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { api, errorMessage } from './api';

/**
 * The slider list (every status, trash included) with its row actions.
 * Actions update the row in place from the server's answer.
 *
 * @return {Object} { items, meta, error, isLoading, reload, replace, drop }.
 */
export default function useSliderList() {
	const [ data, setData ] = useState( null );
	const [ error, setError ] = useState( null );
	const latest = useRef( 0 );

	const reload = useCallback( () => {
		const ticket = ++latest.current;
		setError( null );
		return api.list().then(
			( result ) => ticket === latest.current && setData( result ),
			( e ) => ticket === latest.current && setError( errorMessage( e ) )
		);
	}, [] );

	useEffect( () => {
		reload();
	}, [ reload ] );

	return {
		items: data?.items || [],
		meta: data?.meta || {},
		error,
		isLoading: ! data && ! error,
		reload,
		// Put a row (new or changed) at its place: changed rows stay put,
		// new ones go first.
		replace: ( item ) =>
			setData( ( prev ) => {
				const items = prev?.items || [];
				const exists = items.some( ( row ) => row.id === item.id );
				return {
					...prev,
					items: exists
						? items.map( ( row ) =>
								row.id === item.id ? item : row
							)
						: [ item, ...items ],
				};
			} ),
		drop: ( id ) =>
			setData( ( prev ) => ( {
				...prev,
				items: ( prev?.items || [] ).filter( ( row ) => row.id !== id ),
			} ) ),
	};
}
