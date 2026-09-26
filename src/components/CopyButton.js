/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useCopyToClipboard } from '@wordpress/compose';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { check, copy } from '@wordpress/icons';

/**
 * Icon-only copy button; after copying its icon and label say "Copied".
 *
 * @param {Object} props
 * @param {string} props.value Text to copy.
 * @param {string} props.label Accessible label.
 */
export default function CopyButton( { value, label } ) {
	const [ copied, setCopied ] = useState( false );
	const ref = useCopyToClipboard( value, () => {
		setCopied( true );
		setTimeout( () => setCopied( false ), 2000 );
	} );

	return (
		<Button
			ref={ ref }
			size="compact"
			variant="tertiary"
			icon={ copied ? check : copy }
			label={ copied ? __( 'Copied', 'lw-slider' ) : label }
			showTooltip
		/>
	);
}
