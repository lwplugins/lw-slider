/**
 * WordPress dependencies
 */
import {
	// Same segmented control as the sibling LW admins (no stable equivalent yet).
	/* eslint-disable @wordpress/no-unsafe-wp-apis */
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	/* eslint-enable @wordpress/no-unsafe-wp-apis */
} from '@wordpress/components';

/**
 * Soft segmented control (core ToggleGroupControl, restyled in admin.scss).
 *
 * @param {Object}                  props
 * @param {string}                  props.label    Accessible label.
 * @param {string}                  props.value    Current value.
 * @param {Object[]}                props.options  { value, label }.
 * @param {(value: string) => void} props.onChange Change.
 */
export default function Segmented( { label, value, options, onChange } ) {
	return (
		<ToggleGroupControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			isBlock
			label={ label }
			hideLabelFromVision
			value={ value }
			onChange={ ( next ) => onChange( String( next ) ) }
		>
			{ options.map( ( option ) => (
				<ToggleGroupControlOption
					key={ option.value }
					value={ option.value }
					label={ option.label }
				/>
			) ) }
		</ToggleGroupControl>
	);
}
