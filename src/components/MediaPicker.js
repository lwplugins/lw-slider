/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { image as imageIcon, trash } from '@wordpress/icons';

/**
 * Preview data of a media library attachment (the shape the REST API uses
 * for `images`).
 *
 * @param {Object} attachment wp.media attachment JSON.
 * @return {Object} { id, missing, thumb, medium, alt }.
 */
export const imageFromAttachment = ( attachment ) => {
	const sizes = attachment.sizes || {};
	return {
		id: attachment.id,
		missing: false,
		thumb: ( sizes.thumbnail || sizes.medium || sizes.full || attachment )
			.url,
		medium: ( sizes.medium || sizes.large || sizes.full || attachment ).url,
		alt: attachment.alt || '',
	};
};

/**
 * Image field backed by the media library (wp.media): a preview, then
 * Choose / Replace and Remove. Stores the attachment ID.
 *
 * @param {Object}                               props
 * @param {number}                               props.imageId  Attachment ID (0 = none).
 * @param {Object|undefined}                     props.image    Preview data.
 * @param {(id: number, image: ?Object) => void} props.onChange Change.
 */
export default function MediaPicker( { imageId, image, onChange } ) {
	const frame = useRef( null );

	const open = () => {
		if ( ! window.wp?.media ) {
			return;
		}
		if ( ! frame.current ) {
			frame.current = window.wp.media( {
				title: __( 'Choose a slide image', 'lw-slider' ),
				button: { text: __( 'Use this image', 'lw-slider' ) },
				library: { type: 'image' },
				multiple: false,
			} );
		}
		// Rebind on every open: the callback must see the current slide.
		frame.current.off( 'select' );
		frame.current.on( 'select', () => {
			const attachment = frame.current
				.state()
				.get( 'selection' )
				.first()
				.toJSON();
			onChange( attachment.id, imageFromAttachment( attachment ) );
		} );
		frame.current.open();
	};

	const missing = imageId > 0 && ( ! image || image.missing );

	return (
		<div className="lw-slider-media">
			<div className="lw-slider-media__preview">
				{ imageId > 0 && image && ! image.missing && (
					<img src={ image.medium || image.thumb } alt="" />
				) }
				{ ( ! imageId || missing ) && (
					<span className="lw-slider-media__empty">
						{ missing
							? __(
									'This image is no longer in the media library.',
									'lw-slider'
								)
							: __( 'No image chosen yet.', 'lw-slider' ) }
					</span>
				) }
			</div>
			<div className="lw-admin-inline">
				<Button
					__next40pxDefaultSize
					variant="secondary"
					icon={ imageIcon }
					onClick={ open }
				>
					{ imageId
						? __( 'Replace image', 'lw-slider' )
						: __( 'Choose image', 'lw-slider' ) }
				</Button>
				{ imageId > 0 && (
					<Button
						__next40pxDefaultSize
						variant="tertiary"
						isDestructive
						icon={ trash }
						onClick={ () => onChange( 0, null ) }
					>
						{ __( 'Remove image', 'lw-slider' ) }
					</Button>
				) }
			</div>
		</div>
	);
}
