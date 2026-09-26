/**
 * WordPress dependencies
 */
import { TextControl } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';

const HEX = /^#([A-Fa-f0-9]{3}){1,2}$/;

/**
 * Six-digit form of a #rgb color, for the native color input.
 *
 * @param {string} value Hex color.
 * @return {string} #rrggbb (black when the value is not a color yet).
 */
const long = ( value ) => {
	if ( ! HEX.test( value ) ) {
		return '#000000';
	}
	return value.length === 4
		? `#${ value[ 1 ] }${ value[ 1 ] }${ value[ 2 ] }${ value[ 2 ] }${ value[ 3 ] }${ value[ 3 ] }`
		: value;
};

/**
 * Color picker (native swatch) plus the hex value as text, both editing
 * the same value. What is typed is kept as typed; the server rejects a
 * value that is not #rgb / #rrggbb and the message shows next to the field.
 *
 * @param {Object}                  props
 * @param {string}                  props.label       Accessible label.
 * @param {string}                  props.value       Hex color.
 * @param {(value: string) => void} props.onChange    Change.
 * @param {string}                  props.describedBy Id(s) of the error list.
 * @param {boolean}                 props.invalid     Has errors.
 */
export default function ColorField( {
	label,
	value,
	onChange,
	describedBy,
	invalid,
} ) {
	const id = useInstanceId( ColorField, 'lw-slider-color' );

	return (
		<div className="lw-slider-color">
			<input
				id={ id }
				type="color"
				className="lw-slider-color__swatch"
				aria-label={ label }
				value={ long( value ) }
				onChange={ ( event ) => onChange( event.target.value ) }
			/>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ `${ label } ${ __( '(hex code)', 'lw-slider' ) }` }
				hideLabelFromVision
				className="lw-slider-color__hex"
				value={ value }
				maxLength={ 7 }
				spellCheck={ false }
				aria-describedby={ describedBy }
				aria-invalid={ invalid || undefined }
				onChange={ ( next ) => onChange( next.trim() ) }
			/>
		</div>
	);
}
