/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import FieldErrors from '../../components/FieldErrors';
import Section from '../../components/Section';
import Segmented from '../../components/Segmented';
import SettingRow from '../../components/SettingRow';
import { SwitchItem, SwitchList } from '../../components/Switches';
import { CAN_PUBLISH, LIMITS } from '../../data/boot';
import { NumberRow, TextRow } from './fields';

/**
 * The name, the status and all 17 slider settings.
 *
 * @param {Object} props
 * @param {Object} props.store Editor store.
 */
export default function SettingsTab( { store } ) {
	const { draft, server, errors } = store;
	const s = draft.settings;
	const err = ( key ) => errors[ `settings.${ key }` ];
	const changed = ( key ) => s[ key ] !== server.settings[ key ];
	const toggle = ( key, title, help, children ) => (
		<SwitchItem
			title={ title }
			help={ help }
			checked={ !! s[ key ] }
			changed={ changed( key ) }
			errors={ err( key ) }
			onChange={ ( value ) => store.setSetting( key, value ) }
		>
			{ children }
		</SwitchItem>
	);
	const height = LIMITS.minHeight || [ 100, 1200 ];
	const delay = LIMITS.autoplayDelay || [ 1000, 30000 ];
	const isPublished = draft.status === 'publish';
	const canToggleStatus = CAN_PUBLISH || isPublished;

	return (
		<>
			<Section title={ __( 'General', 'lw-slider' ) }>
				<TextRow
					title={ __( 'Name', 'lw-slider' ) }
					help={ __(
						'Only shown in the admin and the block picker.',
						'lw-slider'
					) }
					value={ draft.title }
					maxLength={ 200 }
					errors={ errors.title }
					onChange={ store.setTitle }
				/>
				<SwitchList>
					<SwitchItem
						title={ __( 'Published', 'lw-slider' ) }
						help={
							canToggleStatus
								? __(
										'Only published sliders show up on the site (shortcode and block). A draft is kept for later.',
										'lw-slider'
									)
								: __(
										'You can save drafts; someone who may publish has to publish this slider.',
										'lw-slider'
									)
						}
						checked={ isPublished }
						disabled={
							! canToggleStatus ||
							! [ 'draft', 'publish' ].includes( draft.status )
						}
						changed={ draft.status !== server.status }
						errors={ errors.status }
						onChange={ ( on ) =>
							store.setStatus( on ? 'publish' : 'draft' )
						}
					/>
				</SwitchList>
			</Section>

			<Section title={ __( 'Size and visibility', 'lw-slider' ) }>
				<NumberRow
					title={ __( 'Minimum height on desktop', 'lw-slider' ) }
					value={ s.min_height_desktop }
					min={ height[ 0 ] }
					max={ height[ 1 ] }
					suffix="px"
					errors={ err( 'min_height_desktop' ) }
					onChange={ ( v ) =>
						store.setSetting( 'min_height_desktop', v )
					}
				/>
				<NumberRow
					title={ __( 'Minimum height on mobile', 'lw-slider' ) }
					help={ __(
						'Used on screens up to 768 px wide.',
						'lw-slider'
					) }
					value={ s.min_height_mobile }
					min={ height[ 0 ] }
					max={ height[ 1 ] }
					suffix="px"
					errors={ err( 'min_height_mobile' ) }
					onChange={ ( v ) =>
						store.setSetting( 'min_height_mobile', v )
					}
				/>
				<SwitchList>
					{ toggle(
						'hide_on_mobile',
						__( 'Hide on mobile', 'lw-slider' ),
						__(
							'The slider is not shown on screens up to 768 px wide.',
							'lw-slider'
						)
					) }
				</SwitchList>
			</Section>

			<Section title={ __( 'Navigation', 'lw-slider' ) }>
				<SwitchList>
					{ toggle(
						'dots',
						__( 'Dots', 'lw-slider' ),
						__( 'One dot per slide under the slider.', 'lw-slider' )
					) }
					{ toggle(
						'arrows',
						__( 'Arrows', 'lw-slider' ),
						__(
							'Previous and next buttons on the sides.',
							'lw-slider'
						),
						s.arrows && (
							<SwitchList>
								{ toggle(
									'arrows_mobile',
									__( 'Arrows on mobile too', 'lw-slider' )
								) }
							</SwitchList>
						)
					) }
					{ toggle(
						'keyboard',
						__( 'Keyboard', 'lw-slider' ),
						__(
							'The left and right arrow keys change the slide while the slider has focus.',
							'lw-slider'
						)
					) }
					{ toggle(
						'swipe',
						__( 'Swipe and drag', 'lw-slider' ),
						__(
							'Change slides with a finger or by dragging with the mouse.',
							'lw-slider'
						)
					) }
				</SwitchList>
			</Section>

			<Section title={ __( 'Playback', 'lw-slider' ) }>
				<SettingRow
					title={ __( 'Transition', 'lw-slider' ) }
					errors={ err( 'transition' ) }
				>
					<Segmented
						label={ __( 'Transition', 'lw-slider' ) }
						value={ s.transition }
						options={ [
							{
								value: 'slide',
								label: __( 'Slide', 'lw-slider' ),
							},
							{ value: 'fade', label: __( 'Fade', 'lw-slider' ) },
						] }
						onChange={ ( v ) =>
							store.setSetting( 'transition', v )
						}
					/>
				</SettingRow>
				<SwitchList>
					{ toggle(
						'loop',
						__( 'Loop', 'lw-slider' ),
						__(
							'After the last slide comes the first one again.',
							'lw-slider'
						)
					) }
					{ toggle(
						'autoplay',
						__( 'Autoplay', 'lw-slider' ),
						__(
							'Slides change on their own. Visitors get a pause button, and playback stops while the slider has keyboard focus, and while they point at it when the option below is on.',
							'lw-slider'
						),
						s.autoplay && (
							<>
								<NumberRow
									title={ __(
										'Time per slide',
										'lw-slider'
									) }
									value={ s.autoplay_delay }
									min={ delay[ 0 ] }
									max={ delay[ 1 ] }
									step={ 500 }
									suffix="ms"
									errors={ err( 'autoplay_delay' ) }
									onChange={ ( v ) =>
										store.setSetting( 'autoplay_delay', v )
									}
								/>
								<SwitchList>
									{ toggle(
										'pause_on_hover',
										__( 'Pause on hover', 'lw-slider' )
									) }
								</SwitchList>
							</>
						)
					) }
				</SwitchList>
			</Section>

			<Section title={ __( 'Content alignment', 'lw-slider' ) }>
				<SettingRow
					title={ __( 'Horizontal', 'lw-slider' ) }
					errors={ err( 'content_align_h' ) }
				>
					<Segmented
						label={ __( 'Horizontal alignment', 'lw-slider' ) }
						value={ s.content_align_h }
						options={ [
							{ value: 'left', label: __( 'Left', 'lw-slider' ) },
							{
								value: 'center',
								label: __( 'Center', 'lw-slider' ),
							},
							{
								value: 'right',
								label: __( 'Right', 'lw-slider' ),
							},
						] }
						onChange={ ( v ) =>
							store.setSetting( 'content_align_h', v )
						}
					/>
				</SettingRow>
				<SettingRow
					title={ __( 'Vertical', 'lw-slider' ) }
					errors={ err( 'content_align_v' ) }
				>
					<Segmented
						label={ __( 'Vertical alignment', 'lw-slider' ) }
						value={ s.content_align_v }
						options={ [
							{ value: 'top', label: __( 'Top', 'lw-slider' ) },
							{
								value: 'center',
								label: __( 'Middle', 'lw-slider' ),
							},
							{
								value: 'bottom',
								label: __( 'Bottom', 'lw-slider' ),
							},
						] }
						onChange={ ( v ) =>
							store.setSetting( 'content_align_v', v )
						}
					/>
				</SettingRow>
			</Section>

			<Section title={ __( 'Advanced', 'lw-slider' ) }>
				<SwitchList>
					{ toggle(
						'use_default_styles',
						__( 'Default text styles', 'lw-slider' ),
						__(
							'White, shadowed headline and text, and a styled button. Turn it off to style them in your theme.',
							'lw-slider'
						)
					) }
				</SwitchList>
				<TextRow
					title={ __( 'Extra CSS class', 'lw-slider' ) }
					help={ __(
						'One class name, added to the slider for your own CSS.',
						'lw-slider'
					) }
					value={ s.custom_class }
					maxLength={ 100 }
					errors={ err( 'custom_class' ) }
					onChange={ ( v ) =>
						store.setSetting( 'custom_class', v.trim() )
					}
				/>
				<FieldErrors errors={ errors.settings } />
			</Section>
		</>
	);
}
