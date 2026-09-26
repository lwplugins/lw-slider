/**
 * WordPress dependencies
 */
import { Button, Modal, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import FieldErrors from '../../components/FieldErrors';
import { api, errorMessage, fieldErrors } from '../../data/api';

/**
 * "New slider" dialog: asks for a name, creates a draft, then opens it.
 *
 * @param {Object}               props
 * @param {() => void}           props.onClose   Close without creating.
 * @param {(id: number) => void} props.onCreated Created: open the editor.
 */
export default function CreateSliderModal( { onClose, onCreated } ) {
	const [ title, setTitle ] = useState( '' );
	const [ busy, setBusy ] = useState( false );
	const [ errors, setErrors ] = useState( [] );

	const submit = async ( event ) => {
		event.preventDefault();
		setBusy( true );
		setErrors( [] );
		try {
			const slider = await api.create( { title: title.trim() } );
			onCreated( slider.id );
		} catch ( e ) {
			setErrors( fieldErrors( e )?.title || [ errorMessage( e ) ] );
			setBusy( false );
		}
	};

	return (
		<Modal
			title={ __( 'New slider', 'lw-slider' ) }
			onRequestClose={ onClose }
			size="small"
		>
			<form className="lw-slider-create" onSubmit={ submit }>
				<TextControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label={ __( 'Name', 'lw-slider' ) }
					help={ __(
						'Only you see it, in this list and in the block picker.',
						'lw-slider'
					) }
					value={ title }
					maxLength={ 200 }
					aria-describedby={
						errors.length ? 'lw-slider-create-errors' : undefined
					}
					aria-invalid={ errors.length > 0 || undefined }
					// The dialog opens for this field.
					// eslint-disable-next-line jsx-a11y/no-autofocus
					autoFocus
					onChange={ setTitle }
				/>
				<FieldErrors errors={ errors } id="lw-slider-create-errors" />
				<div className="lw-admin-inline lw-slider-create__actions">
					<Button
						__next40pxDefaultSize
						variant="tertiary"
						onClick={ onClose }
					>
						{ __( 'Cancel', 'lw-slider' ) }
					</Button>
					<Button
						__next40pxDefaultSize
						variant="primary"
						type="submit"
						isBusy={ busy }
						disabled={ busy }
						accessibleWhenDisabled
					>
						{ __( 'Create slider', 'lw-slider' ) }
					</Button>
				</div>
			</form>
		</Modal>
	);
}
