/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { plus } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import Section from '../../components/Section';
import { copySlide, moveItem, newSlide, slideErrors } from '../../data/slides';
import SlideForm from './SlideForm';
import SlideList from './SlideList';

/**
 * Two panes: the ordered slide list, and the form of the open slide.
 *
 * @param {Object} props
 * @param {Object} props.store Editor store.
 */
export default function SlidesTab( { store } ) {
	const { draft, images, errors } = store;
	const slides = draft.slides;
	const [ selectedKey, setSelectedKey ] = useState( slides[ 0 ]?._key );
	const found = slides.findIndex( ( slide ) => slide._key === selectedKey );
	const index = found === -1 ? 0 : found;
	const slide = slides[ index ];

	const add = () => {
		const created = newSlide();
		store.setSlides( ( list ) => [ ...list, created ] );
		setSelectedKey( created._key );
	};

	if ( ! slides.length ) {
		return (
			<Section className="lw-slider-empty">
				<h2>{ __( 'No slides yet', 'lw-slider' ) }</h2>
				<p className="lw-admin-muted">
					{ __(
						'A slide has a background image or color, and optionally a headline, a text and a link.',
						'lw-slider'
					) }
				</p>
				<Button
					__next40pxDefaultSize
					variant="primary"
					icon={ plus }
					onClick={ add }
				>
					{ __( 'Add the first slide', 'lw-slider' ) }
				</Button>
			</Section>
		);
	}

	return (
		<div className="lw-slides">
			<Section
				className="lw-slides__list"
				title={ __( 'Slides', 'lw-slider' ) }
				description={ __(
					'Drag to reorder, or use the arrows.',
					'lw-slider'
				) }
			>
				<SlideList
					slides={ slides }
					images={ images }
					selectedKey={ slide._key }
					errors={ errors }
					onSelect={ setSelectedKey }
					onMove={ ( from, to ) =>
						store.setSlides( ( list ) =>
							moveItem( list, from, to )
						)
					}
					onAdd={ add }
				/>
			</Section>
			<SlideForm
				key={ slide._key }
				slide={ slide }
				index={ index }
				image={ images[ slide.bg_image_id ] }
				errors={ slideErrors( errors, index ) }
				onChange={ ( values ) => store.setSlide( index, values ) }
				onImage={ store.addImage }
				onDuplicate={ () => {
					const copied = copySlide( slide );
					store.setSlides( ( list ) => [
						...list.slice( 0, index + 1 ),
						copied,
						...list.slice( index + 1 ),
					] );
					setSelectedKey( copied._key );
				} }
				onRemove={ () => {
					const next = slides[ index + 1 ] || slides[ index - 1 ];
					store.setSlides( ( list ) =>
						list.filter( ( item ) => item._key !== slide._key )
					);
					setSelectedKey( next?._key );
				} }
			/>
		</div>
	);
}
