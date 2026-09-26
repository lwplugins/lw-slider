/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useCopyToClipboard } from '@wordpress/compose';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { check, copy } from '@wordpress/icons';

/**
 * Read-only code value with a Copy button. After copying, the button says
 * so for two seconds (icon and text, never a bare glyph).
 *
 * @param {Object} props
 * @param {string} props.value Text to show and copy.
 * @param {string} props.label Accessible name of the value.
 */
export default function CopyField( { value, label } ) {
	const [ copied, setCopied ] = useState( false );
	const ref = useCopyToClipboard( value, () => {
		setCopied( true );
		setTimeout( () => setCopied( false ), 2000 );
	} );

	return (
		<div className="lw-slider-copy">
			<code className="lw-slider-copy__value" aria-label={ label }>
				{ value }
			</code>
			<Button
				ref={ ref }
				__next40pxDefaultSize
				variant="secondary"
				icon={ copied ? check : copy }
			>
				{ copied
					? __( 'Copied', 'lw-slider' )
					: __( 'Copy', 'lw-slider' ) }
			</Button>
		</div>
	);
}
