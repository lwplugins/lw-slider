/**
 * Two-column rows (title + help left, control right) for the editor forms.
 * Each shows the server's validation messages for its field.
 */
/**
 * WordPress dependencies
 */
import {
	SelectControl,
	TextControl,
	TextareaControl,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import { useDescribedByRef } from '../../components/FieldErrors';
import { useErrorIds } from '../../components/Field';
import SettingRow from '../../components/SettingRow';

/**
 * Text row.
 *
 * @param {Object}                  props
 * @param {string}                  props.title     Title (also the input's label).
 * @param {Element}                 props.help      Help.
 * @param {string}                  props.value     Value.
 * @param {(value: string) => void} props.onChange  Change.
 * @param {string[]}                props.errors    Messages.
 * @param {number}                  props.maxLength Longest value.
 * @param {string}                  props.type      Input type.
 * @param {boolean}                 props.multiline Textarea.
 */
export function TextRow( {
	title,
	help,
	value,
	onChange,
	errors = [],
	maxLength,
	type = 'text',
	multiline = false,
} ) {
	const ids = useErrorIds( TextRow, errors );
	const Control = multiline ? TextareaControl : TextControl;

	return (
		<SettingRow
			title={ title }
			help={ help }
			errors={ errors }
			errorId={ ids.errorId }
		>
			<Control
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ title }
				hideLabelFromVision
				type={ multiline ? undefined : type }
				rows={ multiline ? 3 : undefined }
				value={ value ?? '' }
				maxLength={ maxLength }
				aria-describedby={ ids.describedBy }
				aria-invalid={ ids.invalid }
				onChange={ onChange }
			/>
		</SettingRow>
	);
}

/**
 * Whole-number row with a unit. A value that is not a number is sent as
 * typed and the server's message shows under it.
 *
 * @param {Object}                         props
 * @param {string}                         props.title    Title.
 * @param {Element}                        props.help     Help.
 * @param {number|string}                  props.value    Value.
 * @param {(value: number|string) => void} props.onChange Change.
 * @param {string[]}                       props.errors   Messages.
 * @param {number}                         props.min      Minimum.
 * @param {number}                         props.max      Maximum.
 * @param {number}                         props.step     Step.
 * @param {string}                         props.suffix   Unit.
 */
export function NumberRow( {
	title,
	help,
	value,
	onChange,
	errors = [],
	min,
	max,
	step = 1,
	suffix,
} ) {
	const ids = useErrorIds( NumberRow, errors );

	return (
		<SettingRow
			title={ title }
			help={ help }
			errors={ errors }
			errorId={ ids.errorId }
		>
			<div className="lw-admin-inline lw-admin-number">
				<TextControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					type="number"
					label={ title }
					hideLabelFromVision
					min={ min }
					max={ max }
					step={ step }
					value={ String( value ?? '' ) }
					aria-describedby={ ids.describedBy }
					aria-invalid={ ids.invalid }
					onChange={ ( next ) =>
						onChange(
							/^\d{1,9}$/.test( next )
								? parseInt( next, 10 )
								: next
						)
					}
				/>
				{ suffix && <span className="lw-admin-muted">{ suffix }</span> }
			</div>
		</SettingRow>
	);
}

/**
 * Select row.
 *
 * @param {Object}                  props
 * @param {string}                  props.title    Title.
 * @param {Element}                 props.help     Help.
 * @param {string}                  props.value    Value.
 * @param {Object[]}                props.options  { value, label }.
 * @param {(value: string) => void} props.onChange Change.
 * @param {string[]}                props.errors   Messages.
 */
export function SelectRow( {
	title,
	help,
	value,
	options,
	onChange,
	errors = [],
} ) {
	const ids = useErrorIds( SelectRow, errors );
	const ref = useDescribedByRef( ids.describedBy );

	return (
		<SettingRow
			title={ title }
			help={ help }
			errors={ errors }
			errorId={ ids.errorId }
		>
			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				ref={ ref }
				label={ title }
				hideLabelFromVision
				value={ value }
				options={ options }
				aria-invalid={ ids.invalid }
				onChange={ onChange }
			/>
		</SettingRow>
	);
}
