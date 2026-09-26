/**
 * External dependencies
 */
import { DataTable, useTableState } from '@lwplugins/data-table';
import '@lwplugins/data-table/style.css';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { useMemo, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { plus } from '@wordpress/icons';
import { store as noticesStore } from '@wordpress/notices';

/**
 * Internal dependencies
 */
import LoadError from '../../components/LoadError';
import Section from '../../components/Section';
import SliderMark from '../../components/SliderMark';
import {
	SkeletonRegion,
	SkeletonRows,
	SkeletonSection,
} from '../../components/skeleton';
import { api, errorMessage } from '../../data/api';
import { statuses } from '../../data/labels';
import { editorHash } from '../../data/route';
import CreateSliderModal from './CreateSliderModal';
import { listColumns } from './listColumns';
import { tableLabels } from './tableLabels';

/**
 * The slider list (Sliders or Trash view): search, status chips, sort,
 * row actions. Trashing offers Undo in its snackbar.
 *
 * @param {Object}                 props
 * @param {Object}                 props.list     useSliderList() store.
 * @param {boolean}                props.trash    Trash view.
 * @param {boolean}                props.creating The create dialog is open.
 * @param {(hash: string) => void} props.go       Navigate.
 */
export default function SliderList( { list, trash, creating, go } ) {
	const [ busy, setBusy ] = useState( {} );
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );
	const rows = useMemo(
		() =>
			list.items.filter(
				( row ) => ( row.status === 'trash' ) === trash
			),
		[ list.items, trash ]
	);

	const run = async ( row, request, done ) => {
		setBusy( ( prev ) => ( { ...prev, [ row.id ]: true } ) );
		try {
			done( await request( row.id ) );
		} catch ( e ) {
			createErrorNotice( errorMessage( e ), { type: 'snackbar' } );
		}
		setBusy( ( prev ) => ( { ...prev, [ row.id ]: false } ) );
	};

	const actions = {
		duplicate: ( row ) =>
			run( row, api.duplicate, ( copy ) => {
				list.reload();
				createSuccessNotice(
					__( 'Slider duplicated as a draft.', 'lw-slider' ),
					{
						type: 'snackbar',
						actions: [
							{
								label: __( 'Edit the copy', 'lw-slider' ),
								url: editorHash( copy.id ),
							},
						],
					}
				);
			} ),
		trash: ( row ) =>
			run( row, api.trash, ( item ) => {
				list.replace( item );
				createSuccessNotice(
					__( 'Slider moved to the trash.', 'lw-slider' ),
					{
						type: 'snackbar',
						actions: [
							{
								label: __( 'Undo', 'lw-slider' ),
								onClick: () =>
									run( row, api.restore, list.replace ),
							},
						],
					}
				);
			} ),
		restore: ( row ) =>
			run( row, api.restore, ( item ) => {
				list.replace( item );
				createSuccessNotice(
					sprintf(
						/* translators: %s: status the slider got back, e.g. "Draft". */ __(
							'Slider restored (%s).',
							'lw-slider'
						),
						statuses()[ item.status ]?.label || item.status
					),
					{ type: 'snackbar' }
				);
			} ),
		remove: ( row ) =>
			run( row, api.remove, () => {
				list.drop( row.id );
				createSuccessNotice( __( 'Slider deleted.', 'lw-slider' ), {
					type: 'snackbar',
				} );
			} ),
	};

	const columns = listColumns( { trash, actions, busy } );
	const table = useTableState( rows, {
		searchFields: [ 'title', 'shortcode' ],
		columns,
		sort: { field: 'modified', direction: 'desc' },
		perPage: 20,
	} );

	const STATUS = statuses();
	const present = [ ...new Set( rows.map( ( row ) => row.status ) ) ];
	const filters =
		! trash && present.length > 1
			? [
					{
						field: 'status',
						label: __( 'Status', 'lw-slider' ),
						options: present.map( ( value ) => ( {
							value,
							label: STATUS[ value ]?.label || value,
						} ) ),
					},
				]
			: [];

	const modal = creating && (
		<CreateSliderModal
			onClose={ () => go( '#sliders' ) }
			onCreated={ ( id ) => go( editorHash( id ) ) }
		/>
	);

	if ( list.error ) {
		return <LoadError message={ list.error } onRetry={ list.reload } />;
	}

	if ( list.isLoading ) {
		return (
			<>
				<SkeletonRegion className="lw-skel-tab">
					<SkeletonSection description={ false }>
						<SkeletonRows count={ 5 } />
					</SkeletonSection>
				</SkeletonRegion>
				{ modal }
			</>
		);
	}

	if ( ! trash && ! rows.length ) {
		return (
			<>
				<Section className="lw-slider-empty">
					<SliderMark />
					<h2>{ __( 'Create your first slider', 'lw-slider' ) }</h2>
					<p className="lw-admin-muted">
						{ __(
							'Add slides with an image or a color, a headline and a link, then show the slider with its shortcode or the LW Slider block.',
							'lw-slider'
						) }
					</p>
					<Button
						__next40pxDefaultSize
						variant="primary"
						icon={ plus }
						href="#new"
					>
						{ __( 'New slider', 'lw-slider' ) }
					</Button>
				</Section>
				{ modal }
			</>
		);
	}

	return (
		<>
			<Section>
				<DataTable
					columns={ columns }
					table={ table }
					filters={ filters }
					caption={
						trash
							? __( 'Sliders in the trash', 'lw-slider' )
							: __( 'Sliders', 'lw-slider' )
					}
					getRowLabel={ ( row ) => row.title }
					labels={ tableLabels( trash ) }
				/>
			</Section>
			{ modal }
		</>
	);
}
