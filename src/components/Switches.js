/**
 * WordPress dependencies
 */
import { FormToggle } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import FieldErrors, { describedBy } from './FieldErrors';

/**
 * A list of switches. Each switch sits at the start of its own full-width
 * row (never alone in the right column of a two-column row).
 *
 * @param {Object}  props
 * @param {Element} props.children SwitchItem rows.
 */
export function SwitchList( { children } ) {
	return <ul className="lw-slider-switches">{ children }</ul>;
}

/**
 * One switch row: switch, title (its label), help, an optional badge and an
 * optional nested field that only matters while the switch is on.
 *
 * @param {Object}                     props
 * @param {string}                     props.title    Setting name.
 * @param {Element}                    props.help     Description.
 * @param {boolean}                    props.checked  Value.
 * @param {(checked: boolean) => void} props.onChange Receives the new boolean.
 * @param {boolean}                    props.disabled Disabled.
 * @param {Element}                    props.badge    Optional badge after the title.
 * @param {string[]}                   props.errors   Validation messages.
 * @param {boolean}                    props.changed  Differs from the saved value.
 * @param {Element}                    props.children Nested field.
 */
export function SwitchItem( {
	title,
	help,
	checked,
	onChange,
	disabled = false,
	badge,
	errors = [],
	changed = false,
	children,
} ) {
	const id = useInstanceId( SwitchItem, 'lw-slider-switch' );
	const classes = [
		'lw-slider-switch',
		checked ? 'is-on' : 'is-off',
		changed ? 'is-changed' : '',
		errors.length ? 'has-error' : '',
	];

	return (
		<li className={ classes.filter( Boolean ).join( ' ' ) }>
			<FormToggle
				id={ id }
				checked={ checked }
				disabled={ disabled }
				aria-describedby={ describedBy(
					help && `${ id }-help`,
					errors.length > 0 && `${ id }-errors`
				) }
				aria-invalid={ errors.length > 0 || undefined }
				onChange={ ( event ) => onChange( event.target.checked ) }
			/>
			<div className="lw-slider-switch__body">
				<div className="lw-slider-switch__head">
					<label className="lw-slider-switch__title" htmlFor={ id }>
						{ title }
					</label>
					{ badge }
				</div>
				{ help && (
					<p className="lw-slider-switch__help" id={ `${ id }-help` }>
						{ help }
					</p>
				) }
				<FieldErrors errors={ errors } id={ `${ id }-errors` } />
				{ children && (
					<div className="lw-slider-switch__nested">{ children }</div>
				) }
			</div>
		</li>
	);
}
