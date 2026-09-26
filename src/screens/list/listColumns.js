/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { backup, copy, pencil, trash } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import ConfirmButton from '../../components/ConfirmButton';
import CopyButton from '../../components/CopyButton';
import StatusBadge from '../../components/StatusBadge';
import { fullDate, timeAgo } from '../../data/format';
import { statuses } from '../../data/labels';
import { editorHash } from '../../data/route';

const name = ( row ) => row.title || __( '(no title)', 'lw-slider' );

/**
 * Columns of the slider list.
 *
 * @param {Object}  props
 * @param {boolean} props.trash   Trash view (restore / delete for good).
 * @param {Object}  props.actions { duplicate, trash, restore, remove }( row ).
 * @param {Object}  props.busy    Row IDs with a request in flight.
 * @return {Object[]} Columns.
 */
export function listColumns( { trash: isTrash, actions, busy } ) {
	const STATUS = statuses();

	return [
		{
			id: 'title',
			label: __( 'Slider', 'lw-slider' ),
			sortable: true,
			defaultSortDirection: 'asc',
			sortValue: ( row ) => name( row ).toLocaleLowerCase(),
			render: ( row ) => (
				<div className="lw-slider-row">
					<span className="lw-slider-row__thumb" aria-hidden="true">
						{ row.thumb && <img src={ row.thumb } alt="" /> }
					</span>
					<span className="lw-admin-stack">
						{ isTrash ? (
							<strong>{ name( row ) }</strong>
						) : (
							<a
								className="lw-slider-row__title"
								href={ editorHash( row.id ) }
							>
								{ name( row ) }
							</a>
						) }
						{ ! isTrash && row.status !== 'publish' && (
							<span>
								<StatusBadge
									status={ STATUS[ row.status ]?.tone }
								>
									{ STATUS[ row.status ]?.label ||
										row.status }
								</StatusBadge>
							</span>
						) }
					</span>
				</div>
			),
		},
		{
			id: 'slides',
			label: __( 'Slides', 'lw-slider' ),
			sortable: true,
			sortValue: ( row ) => row.slides.total,
			render: ( row ) =>
				sprintf(
					/* translators: 1: active slides, 2: all slides. */
					__( '%1$d of %2$d active', 'lw-slider' ),
					row.slides.active,
					row.slides.total
				),
		},
		{
			id: 'shortcode',
			label: __( 'Shortcode', 'lw-slider' ),
			render: ( row ) => (
				<span className="lw-admin-inline lw-admin-nowrap">
					<code className="lw-admin-code">{ row.shortcode }</code>
					<CopyButton
						value={ row.shortcode }
						label={ sprintf(
							/* translators: %s: slider name. */ __(
								'Copy the shortcode of %s',
								'lw-slider'
							),
							name( row )
						) }
					/>
				</span>
			),
		},
		{
			id: 'modified',
			label: __( 'Changed', 'lw-slider' ),
			sortable: true,
			defaultSortDirection: 'desc',
			render: ( row ) => (
				<span title={ fullDate( row.modified ) }>
					{ timeAgo( row.modified ) }
				</span>
			),
		},
		{
			id: 'actions',
			label: __( 'Actions', 'lw-slider' ),
			align: 'end',
			render: ( row ) =>
				isTrash ? (
					<span className="lw-admin-inline lw-slider-row__actions">
						<Button
							size="compact"
							variant="secondary"
							icon={ backup }
							disabled={ busy[ row.id ] || ! row.can_delete }
							accessibleWhenDisabled
							onClick={ () => actions.restore( row ) }
						>
							{ __( 'Restore', 'lw-slider' ) }
						</Button>
						<ConfirmButton
							size="compact"
							variant="tertiary"
							isDestructive
							icon={ trash }
							disabled={ busy[ row.id ] || ! row.can_delete }
							accessibleWhenDisabled
							question={ sprintf(
								/* translators: %s: slider name. */
								__(
									'Delete "%s" for good? This cannot be undone.',
									'lw-slider'
								),
								name( row )
							) }
							confirmText={ __( 'Delete for good', 'lw-slider' ) }
							onConfirm={ () => actions.remove( row ) }
						>
							{ __( 'Delete for good', 'lw-slider' ) }
						</ConfirmButton>
					</span>
				) : (
					<span className="lw-admin-inline lw-slider-row__actions">
						<Button
							size="compact"
							variant="tertiary"
							icon={ pencil }
							href={ editorHash( row.id ) }
							label={ sprintf(
								/* translators: %s: slider name. */ __(
									'Edit %s',
									'lw-slider'
								),
								name( row )
							) }
						/>
						<Button
							size="compact"
							variant="tertiary"
							icon={ copy }
							disabled={ busy[ row.id ] }
							accessibleWhenDisabled
							label={ sprintf(
								/* translators: %s: slider name. */ __(
									'Duplicate %s',
									'lw-slider'
								),
								name( row )
							) }
							onClick={ () => actions.duplicate( row ) }
						/>
						<Button
							size="compact"
							variant="tertiary"
							isDestructive
							icon={ trash }
							disabled={ busy[ row.id ] || ! row.can_delete }
							accessibleWhenDisabled
							label={ sprintf(
								/* translators: %s: slider name. */ __(
									'Move %s to the trash',
									'lw-slider'
								),
								name( row )
							) }
							onClick={ () => actions.trash( row ) }
						/>
					</span>
				),
		},
	];
}
