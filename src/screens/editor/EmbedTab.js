/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { copy, published, trash } from '@wordpress/icons';
import { store as noticesStore } from '@wordpress/notices';

/**
 * Internal dependencies
 */
import Callout from '../../components/Callout';
import ConfirmButton from '../../components/ConfirmButton';
import CopyField from '../../components/CopyField';
import Section from '../../components/Section';
import { api, errorMessage } from '../../data/api';
import { CAN_PUBLISH } from '../../data/boot';
import { editorHash } from '../../data/route';

/**
 * How to show the slider (shortcode, block, PHP), plus duplicate and trash.
 *
 * @param {Object}                 props
 * @param {Object}                 props.store  Editor store.
 * @param {(hash: string) => void} props.go     Navigate (skips the unsaved-changes guard).
 * @param {() => void}             props.onGone Called after the slider left (trash).
 */
export default function EmbedTab( { store, go, onGone } ) {
	const { server, draft } = store;
	const [ busy, setBusy ] = useState( false );
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );
	const php = `<?php echo do_shortcode( '${ server.shortcode.replace( /'/g, "\\'" ) }' ); ?>`;

	const act = async ( request, done ) => {
		setBusy( true );
		try {
			done( await request( server.id ) );
		} catch ( e ) {
			createErrorNotice( errorMessage( e ), { type: 'snackbar' } );
		}
		setBusy( false );
	};

	return (
		<>
			{ server.status !== 'publish' && (
				<Callout tone="warning">
					<p className="lw-admin-muted">
						{ __(
							'This slider is not published yet, so the shortcode and the block show nothing on the site.',
							'lw-slider'
						) }
					</p>
					{ CAN_PUBLISH && server.status === 'draft' && (
						<Button
							__next40pxDefaultSize
							variant="secondary"
							icon={ published }
							isBusy={ store.isSaving }
							disabled={ store.isSaving }
							accessibleWhenDisabled
							onClick={ () =>
								store.save( { status: 'publish' } )
							}
						>
							{ store.hasEdits
								? __( 'Publish and save changes', 'lw-slider' )
								: __( 'Publish', 'lw-slider' ) }
						</Button>
					) }
				</Callout>
			) }

			<Section
				title={ __( 'Shortcode', 'lw-slider' ) }
				description={ __(
					'Paste it into a post, a page, or a text widget.',
					'lw-slider'
				) }
			>
				<CopyField
					value={ server.shortcode }
					label={ __( 'Shortcode', 'lw-slider' ) }
				/>
			</Section>

			<Section title={ __( 'Block', 'lw-slider' ) }>
				<p className="lw-admin-muted">
					{ __(
						'In the block editor, add the LW Slider block and choose this slider. The block can also override autoplay, the transition, dots, arrows, loop and the height for that one place.',
						'lw-slider'
					) }
				</p>
			</Section>

			<Section
				title={ __( 'Theme template', 'lw-slider' ) }
				description={ __(
					'For developers: print the slider from a PHP template.',
					'lw-slider'
				) }
			>
				<CopyField
					value={ php }
					label={ __( 'PHP code', 'lw-slider' ) }
				/>
			</Section>

			<Section title={ __( 'Slider actions', 'lw-slider' ) }>
				<div className="lw-admin-inline">
					<Button
						__next40pxDefaultSize
						variant="secondary"
						icon={ copy }
						disabled={ busy }
						accessibleWhenDisabled
						onClick={ () =>
							act( api.duplicate, ( slider ) => {
								createSuccessNotice(
									__(
										'Slider duplicated as a draft.',
										'lw-slider'
									),
									{ type: 'snackbar' }
								);
								// Through the hash, so unsaved changes here are asked about first.
								window.location.hash = editorHash( slider.id );
							} )
						}
					>
						{ store.hasEdits
							? __( 'Duplicate the saved version', 'lw-slider' )
							: __( 'Duplicate', 'lw-slider' ) }
					</Button>
					{ server.can_delete && (
						<ConfirmButton
							__next40pxDefaultSize
							variant="secondary"
							isDestructive
							icon={ trash }
							disabled={ busy }
							accessibleWhenDisabled
							question={
								store.hasEdits
									? __(
											'Move this slider to the trash? Your unsaved changes are lost.',
											'lw-slider'
										)
									: __(
											'Move this slider to the trash? You can restore it from the Trash.',
											'lw-slider'
										)
							}
							confirmText={ __( 'Move to trash', 'lw-slider' ) }
							onConfirm={ () =>
								act( api.trash, () => {
									createSuccessNotice(
										__(
											'Slider moved to the trash.',
											'lw-slider'
										),
										{ type: 'snackbar' }
									);
									store.discard();
									onGone();
									go( '#sliders' );
								} )
							}
						>
							{ __( 'Move to trash', 'lw-slider' ) }
						</ConfirmButton>
					) }
				</div>
				{ draft.title !== server.title && (
					<p className="lw-admin-hint">
						{ __(
							'Actions use the saved version of the slider.',
							'lw-slider'
						) }
					</p>
				) }
			</Section>
		</>
	);
}
