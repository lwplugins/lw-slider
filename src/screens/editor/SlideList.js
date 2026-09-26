/**
 * WordPress dependencies
 */
import { speak } from '@wordpress/a11y';
import { Button } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import {
	Icon,
	caution,
	chevronDown,
	chevronUp,
	dragHandle,
	plus,
} from '@wordpress/icons';

/**
 * Internal dependencies
 */
import StatusBadge from '../../components/StatusBadge';
import { slideName } from '../../data/slides';

/**
 * The ordered slide list: select a slide, reorder by drag and drop (mouse)
 * or with Move up / Move down (keyboard; focus stays on the moved slide's
 * button and the new position is announced), add a slide.
 *
 * @param {Object}                             props
 * @param {Object[]}                           props.slides      Keyed slides.
 * @param {Object}                             props.images      Image previews by ID.
 * @param {string}                             props.selectedKey Key of the open slide.
 * @param {Object}                             props.errors      All field errors.
 * @param {(key: string) => void}              props.onSelect    Open a slide.
 * @param {(from: number, to: number) => void} props.onMove      Move a slide.
 * @param {() => void}                         props.onAdd       Add a slide.
 */
export default function SlideList( {
	slides,
	images,
	selectedKey,
	errors,
	onSelect,
	onMove,
	onAdd,
} ) {
	const [ drag, setDrag ] = useState( null );
	const [ over, setOver ] = useState( null );
	const buttons = useRef( {} );
	const refocus = useRef( null );

	useEffect( () => {
		if ( refocus.current ) {
			buttons.current[ refocus.current.key ]?.[
				refocus.current.which
			]?.focus();
			refocus.current = null;
		}
	}, [ slides ] );

	const move = ( index, to, which ) => {
		if ( to < 0 || to >= slides.length ) {
			return;
		}
		refocus.current = { key: slides[ index ]._key, which };
		onMove( index, to );
		speak(
			sprintf(
				/* translators: 1: slide name, 2: new position, 3: number of slides. */
				__( '%1$s moved to position %2$d of %3$d.', 'lw-slider' ),
				slideName( slides[ index ], index ),
				to + 1,
				slides.length
			)
		);
	};

	const hasErrors = ( index ) =>
		Object.keys( errors ).some(
			( key ) =>
				key === `slides.${ index }` ||
				key.startsWith( `slides.${ index }.` )
		);

	return (
		<div className="lw-slide-list">
			<ol className="lw-slide-list__items">
				{ slides.map( ( slide, index ) => {
					const image =
						slide.bg_type === 'image'
							? images[ slide.bg_image_id ]
							: null;
					const name = slideName( slide, index );
					const classes = [
						'lw-slide-item',
						slide._key === selectedKey ? 'is-current' : '',
						slide.active ? '' : 'is-inactive',
						drag === index ? 'is-dragging' : '',
						over === index && drag !== null && drag !== index
							? 'is-over'
							: '',
					];

					return (
						<li
							key={ slide._key }
							className={ classes.filter( Boolean ).join( ' ' ) }
							draggable
							onDragStart={ ( event ) => {
								event.dataTransfer.effectAllowed = 'move';
								event.dataTransfer.setData(
									'text/plain',
									String( index )
								);
								setDrag( index );
							} }
							onDragOver={ ( event ) => {
								event.preventDefault();
								setOver( index );
							} }
							onDrop={ ( event ) => {
								event.preventDefault();
								if ( drag !== null && drag !== index ) {
									onMove( drag, index );
								}
								setDrag( null );
								setOver( null );
							} }
							onDragEnd={ () => {
								setDrag( null );
								setOver( null );
							} }
						>
							<span
								className="lw-slide-item__handle"
								aria-hidden="true"
							>
								<Icon icon={ dragHandle } size={ 20 } />
							</span>
							<button
								type="button"
								className="lw-slide-item__select"
								aria-current={
									slide._key === selectedKey
										? 'true'
										: undefined
								}
								ref={ ( node ) => {
									buttons.current[ slide._key ] = {
										...buttons.current[ slide._key ],
										select: node,
									};
								} }
								onClick={ () => onSelect( slide._key ) }
							>
								<span
									className="lw-slide-item__thumb"
									style={
										slide.bg_type === 'color'
											? { background: slide.bg_color }
											: undefined
									}
								>
									{ image?.thumb && ! image.missing && (
										<img src={ image.thumb } alt="" />
									) }
								</span>
								<span className="lw-slide-item__text">
									<span className="lw-slide-item__name">
										{ name }
									</span>
									<span className="lw-slide-item__meta">
										{ sprintf(
											/* translators: %d: slide position. */
											__( 'Slide %d', 'lw-slider' ),
											index + 1
										) }
									</span>
								</span>
								{ ! slide.active && (
									<StatusBadge status="idle">
										{ __( 'Off', 'lw-slider' ) }
									</StatusBadge>
								) }
								{ hasErrors( index ) && (
									<span className="lw-admin-navflag">
										<Icon icon={ caution } size={ 18 } />
										<span className="screen-reader-text">
											{ __(
												'Has invalid fields',
												'lw-slider'
											) }
										</span>
									</span>
								) }
							</button>
							<span className="lw-slide-item__move">
								<Button
									size="small"
									icon={ chevronUp }
									label={ sprintf(
										/* translators: %s: slide name. */ __(
											'Move up: %s',
											'lw-slider'
										),
										name
									) }
									disabled={ index === 0 }
									accessibleWhenDisabled
									ref={ ( node ) => {
										buttons.current[ slide._key ] = {
											...buttons.current[ slide._key ],
											up: node,
										};
									} }
									onClick={ () =>
										move(
											index,
											index - 1,
											index - 1 === 0 ? 'down' : 'up'
										)
									}
								/>
								<Button
									size="small"
									icon={ chevronDown }
									label={ sprintf(
										/* translators: %s: slide name. */ __(
											'Move down: %s',
											'lw-slider'
										),
										name
									) }
									disabled={ index === slides.length - 1 }
									accessibleWhenDisabled
									ref={ ( node ) => {
										buttons.current[ slide._key ] = {
											...buttons.current[ slide._key ],
											down: node,
										};
									} }
									onClick={ () =>
										move(
											index,
											index + 1,
											index + 1 === slides.length - 1
												? 'up'
												: 'down'
										)
									}
								/>
							</span>
						</li>
					);
				} ) }
			</ol>
			<Button
				__next40pxDefaultSize
				variant="secondary"
				icon={ plus }
				className="lw-slide-list__add"
				onClick={ onAdd }
			>
				{ __( 'Add slide', 'lw-slider' ) }
			</Button>
		</div>
	);
}
