/**
 * WordPress dependencies
 */
import { Button, RangeControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { copy, trash } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import ColorField from '../../components/ColorField';
import ConfirmButton from '../../components/ConfirmButton';
import { useErrorIds } from '../../components/Field';
import FieldErrors from '../../components/FieldErrors';
import MediaPicker from '../../components/MediaPicker';
import Section from '../../components/Section';
import Segmented from '../../components/Segmented';
import SettingRow from '../../components/SettingRow';
import { SwitchItem, SwitchList } from '../../components/Switches';
import { slideName } from '../../data/slides';
import { SelectRow, TextRow } from './fields';

const positions = () => [
	{ value: 'left top', label: __( 'Top left', 'lw-slider' ) },
	{ value: 'center top', label: __( 'Top center', 'lw-slider' ) },
	{ value: 'right top', label: __( 'Top right', 'lw-slider' ) },
	{ value: 'left center', label: __( 'Middle left', 'lw-slider' ) },
	{ value: 'center center', label: __( 'Center', 'lw-slider' ) },
	{ value: 'right center', label: __( 'Middle right', 'lw-slider' ) },
	{ value: 'left bottom', label: __( 'Bottom left', 'lw-slider' ) },
	{ value: 'center bottom', label: __( 'Bottom center', 'lw-slider' ) },
	{ value: 'right bottom', label: __( 'Bottom right', 'lw-slider' ) },
];

/**
 * Color row (swatch + hex text) with its error wiring.
 *
 * @param {Object}                  props
 * @param {string}                  props.title    Title.
 * @param {string}                  props.value    Hex color.
 * @param {(value: string) => void} props.onChange Change.
 * @param {string[]}                props.errors   Messages.
 */
function ColorRow( { title, value, onChange, errors = [] } ) {
	const ids = useErrorIds( ColorRow, errors );

	return (
		<SettingRow title={ title } errors={ errors } errorId={ ids.errorId }>
			<ColorField
				label={ title }
				value={ value }
				onChange={ onChange }
				describedBy={ ids.describedBy }
				invalid={ ids.invalid }
			/>
		</SettingRow>
	);
}

/**
 * The form of one slide: state, background, content, overlay, link. Fields
 * that do not apply (color fields of an image slide, button text of a
 * full-slide link…) are not shown.
 *
 * @param {Object}                   props
 * @param {Object}                   props.slide       Slide.
 * @param {number}                   props.index       Position.
 * @param {Object|undefined}         props.image       Preview of its image.
 * @param {Object}                   props.errors      This slide's errors by field.
 * @param {(values: Object) => void} props.onChange    Change fields.
 * @param {(image: Object) => void}  props.onImage     Remember a picked image.
 * @param {() => void}               props.onDuplicate Duplicate the slide.
 * @param {() => void}               props.onRemove    Remove the slide.
 */
export default function SlideForm( {
	slide,
	index,
	image,
	errors,
	onChange,
	onImage,
	onDuplicate,
	onRemove,
} ) {
	const set = ( key ) => ( value ) => onChange( { [ key ]: value } );
	const name = slideName( slide, index );
	const hasLink = '' !== ( slide.link_url || '' ).trim();
	const hasOverlay = '' !== ( slide.overlay_color || '' );

	return (
		<div className="lw-slide-form">
			<Section
				title={ name }
				actions={
					<div className="lw-admin-inline">
						<Button
							size="compact"
							variant="tertiary"
							icon={ copy }
							onClick={ onDuplicate }
						>
							{ __( 'Duplicate', 'lw-slider' ) }
						</Button>
						<ConfirmButton
							size="compact"
							variant="tertiary"
							isDestructive
							icon={ trash }
							question={ sprintf(
								/* translators: %s: slide name. */
								__(
									'Remove "%s" from this slider?',
									'lw-slider'
								),
								name
							) }
							confirmText={ __( 'Remove slide', 'lw-slider' ) }
							onConfirm={ onRemove }
						>
							{ __( 'Remove', 'lw-slider' ) }
						</ConfirmButton>
					</div>
				}
			>
				<FieldErrors errors={ errors._slide } />
				<SwitchList>
					<SwitchItem
						title={ __( 'Active', 'lw-slider' ) }
						help={ __(
							'Inactive slides are kept here but not shown on the site.',
							'lw-slider'
						) }
						checked={ !! slide.active }
						errors={ errors.active }
						onChange={ set( 'active' ) }
					/>
				</SwitchList>
			</Section>

			<Section title={ __( 'Background', 'lw-slider' ) }>
				<SettingRow
					title={ __( 'Type', 'lw-slider' ) }
					errors={ errors.bg_type }
				>
					<Segmented
						label={ __( 'Background type', 'lw-slider' ) }
						value={ slide.bg_type }
						options={ [
							{
								value: 'image',
								label: __( 'Image', 'lw-slider' ),
							},
							{
								value: 'color',
								label: __( 'Color', 'lw-slider' ),
							},
						] }
						onChange={ set( 'bg_type' ) }
					/>
				</SettingRow>
				{ slide.bg_type === 'image' ? (
					<>
						<SettingRow
							title={ __( 'Image', 'lw-slider' ) }
							stacked
							errors={ errors.bg_image_id }
						>
							<MediaPicker
								imageId={ Number( slide.bg_image_id ) || 0 }
								image={ image }
								onChange={ ( id, picked ) => {
									onImage( picked );
									onChange( { bg_image_id: id } );
								} }
							/>
						</SettingRow>
						<SelectRow
							title={ __( 'Focus point', 'lw-slider' ) }
							help={ __(
								'The part of the image that stays visible when the slide crops it.',
								'lw-slider'
							) }
							value={ slide.bg_position }
							options={ positions() }
							errors={ errors.bg_position }
							onChange={ set( 'bg_position' ) }
						/>
						<TextRow
							title={ __( 'Alternative text', 'lw-slider' ) }
							help={ __(
								'Describe the image for screen readers. Leave it empty for a decorative image; the media library text is used then.',
								'lw-slider'
							) }
							value={ slide.image_alt }
							maxLength={ 500 }
							errors={ errors.image_alt }
							onChange={ set( 'image_alt' ) }
						/>
					</>
				) : (
					<ColorRow
						title={ __( 'Background color', 'lw-slider' ) }
						value={ slide.bg_color }
						errors={ errors.bg_color }
						onChange={ set( 'bg_color' ) }
					/>
				) }
			</Section>

			<Section title={ __( 'Content', 'lw-slider' ) }>
				<TextRow
					title={ __( 'Headline', 'lw-slider' ) }
					value={ slide.headline }
					maxLength={ 500 }
					errors={ errors.headline }
					onChange={ set( 'headline' ) }
				/>
				<TextRow
					title={ __( 'Subheadline', 'lw-slider' ) }
					value={ slide.subheadline }
					maxLength={ 500 }
					errors={ errors.subheadline }
					onChange={ set( 'subheadline' ) }
				/>
				<TextRow
					title={ __( 'Description', 'lw-slider' ) }
					value={ slide.description }
					maxLength={ 5000 }
					multiline
					errors={ errors.description }
					onChange={ set( 'description' ) }
				/>
			</Section>

			<Section
				title={ __( 'Overlay', 'lw-slider' ) }
				description={ __(
					'A color layer over the background that makes light text easier to read.',
					'lw-slider'
				) }
			>
				<SwitchList>
					<SwitchItem
						title={ __( 'Overlay', 'lw-slider' ) }
						checked={ hasOverlay }
						errors={ errors.overlay_color }
						onChange={ ( on ) =>
							onChange( { overlay_color: on ? '#000000' : '' } )
						}
					>
						{ hasOverlay && (
							<div className="lw-slider-overlay-fields">
								<ColorField
									label={ __( 'Overlay color', 'lw-slider' ) }
									value={ slide.overlay_color }
									onChange={ set( 'overlay_color' ) }
								/>
								<RangeControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									label={ __( 'Opacity (%)', 'lw-slider' ) }
									min={ 0 }
									max={ 100 }
									value={ Number( slide.overlay_opacity ) }
									onChange={ ( value ) =>
										onChange( {
											overlay_opacity: Number(
												value ?? 0
											),
										} )
									}
								/>
								<FieldErrors
									errors={ errors.overlay_opacity }
								/>
							</div>
						) }
					</SwitchItem>
				</SwitchList>
			</Section>

			<Section title={ __( 'Link', 'lw-slider' ) }>
				<TextRow
					title={ __( 'Link address', 'lw-slider' ) }
					help={ __(
						'Leave it empty for a slide without a link.',
						'lw-slider'
					) }
					type="url"
					value={ slide.link_url }
					maxLength={ 2048 }
					errors={ errors.link_url }
					onChange={ set( 'link_url' ) }
				/>
				{ hasLink && (
					<>
						<SettingRow
							title={ __( 'Clickable area', 'lw-slider' ) }
							errors={ errors.cta_mode }
						>
							<Segmented
								label={ __( 'Clickable area', 'lw-slider' ) }
								value={ slide.cta_mode }
								options={ [
									{
										value: 'full_slide',
										label: __( 'Whole slide', 'lw-slider' ),
									},
									{
										value: 'button',
										label: __( 'Button', 'lw-slider' ),
									},
								] }
								onChange={ set( 'cta_mode' ) }
							/>
						</SettingRow>
						{ slide.cta_mode === 'button' && (
							<TextRow
								title={ __( 'Button text', 'lw-slider' ) }
								help={ __(
									'The button only shows when it has a text.',
									'lw-slider'
								) }
								value={ slide.button_text }
								maxLength={ 500 }
								errors={ errors.button_text }
								onChange={ set( 'button_text' ) }
							/>
						) }
						<SwitchList>
							<SwitchItem
								title={ __( 'Open in a new tab', 'lw-slider' ) }
								checked={ slide.link_target === '_blank' }
								errors={ errors.link_target }
								onChange={ ( on ) =>
									onChange( {
										link_target: on ? '_blank' : '_self',
									} )
								}
							/>
						</SwitchList>
					</>
				) }
			</Section>
		</div>
	);
}
