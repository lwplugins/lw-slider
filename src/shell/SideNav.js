/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Icon,
	chevronDown,
	chevronRight,
	code,
	external,
	gallery,
	help,
	settings,
	trash,
} from '@wordpress/icons';
import { Badge } from '@wordpress/ui';

/**
 * Internal dependencies
 */
import SliderMark from '../components/SliderMark';
import { DOCS_URL, VERSION } from '../data/boot';
import { editorHash } from '../data/route';

/**
 * One nav link.
 *
 * @param {Object}  props
 * @param {Object}  props.item      { id, href, label, icon }.
 * @param {boolean} props.isCurrent Active.
 * @param {Element} props.meta      Optional node after the label.
 */
function NavItem( { item, isCurrent, meta } ) {
	return (
		<li>
			<a
				href={ item.href }
				className="lw-admin-sidenav__item"
				aria-current={ isCurrent ? 'page' : undefined }
			>
				<Icon icon={ item.icon } size={ 20 } />
				<span className="lw-admin-sidenav__label">{ item.label }</span>
				{ meta && (
					<span className="lw-admin-sidenav__meta">{ meta }</span>
				) }
				{ isCurrent && <Icon icon={ chevronRight } size={ 18 } /> }
			</a>
		</li>
	);
}

/**
 * Full-height sidebar: plugin header, the list views, and, while a slider is
 * open, its sections under its name. On mobile the list collapses behind a
 * "current section" toggle.
 *
 * @param {Object}      props
 * @param {Object}      props.route  Current route.
 * @param {Object|null} props.editor { id, title, meta: { tab: node } } while editing.
 * @param {Object}      props.counts { sliders, trash } (null while loading).
 */
export default function SideNav( { route, editor, counts } ) {
	const [ isOpen, setIsOpen ] = useState( false );
	const navId = useInstanceId( SideNav, 'lw-admin-sidenav' );

	const main = [
		{
			id: 'sliders',
			href: '#sliders',
			label: __( 'Sliders', 'lw-slider' ),
			icon: gallery,
		},
		{
			id: 'trash',
			href: '#trash',
			label: __( 'Trash', 'lw-slider' ),
			icon: trash,
		},
	];
	const sections = editor
		? [
				{
					id: 'slides',
					href: editorHash( editor.id ),
					label: __( 'Slides', 'lw-slider' ),
					icon: gallery,
				},
				{
					id: 'settings',
					href: editorHash( editor.id, 'settings' ),
					label: __( 'Settings', 'lw-slider' ),
					icon: settings,
				},
				{
					id: 'embed',
					href: editorHash( editor.id, 'embed' ),
					label: __( 'Embed', 'lw-slider' ),
					icon: code,
				},
			]
		: [];

	const currentMain = route.view === 'trash' ? 'trash' : 'sliders';
	const active =
		( route.view === 'editor' &&
			sections.find( ( s ) => s.id === route.tab ) ) ||
		main.find( ( m ) => m.id === currentMain );

	useEffect( () => setIsOpen( false ), [ route ] );

	return (
		<aside className={ `lw-admin-sidebar ${ isOpen ? 'is-open' : '' }` }>
			<div className="lw-admin-sidebar__head">
				<a
					className="lw-admin-sidebar__home"
					href="#sliders"
					aria-label={ __( 'LW Slider home', 'lw-slider' ) }
				>
					<SliderMark />
					<strong>LW Slider</strong>
				</a>
				<Badge intent="informational">{ `v${ VERSION }` }</Badge>
			</div>
			<button
				type="button"
				className="lw-admin-sidebar__toggle"
				aria-expanded={ isOpen }
				aria-controls={ navId }
				onClick={ () => setIsOpen( ! isOpen ) }
			>
				<Icon icon={ active.icon } size={ 20 } />
				<span>{ active.label }</span>
				<Icon icon={ chevronDown } size={ 20 } />
			</button>
			<nav
				id={ navId }
				className="lw-admin-sidenav"
				aria-label={ __( 'LW Slider sections', 'lw-slider' ) }
			>
				<ul>
					{ main.map( ( item ) => (
						<NavItem
							key={ item.id }
							item={ item }
							isCurrent={
								route.view !== 'editor' &&
								item.id === currentMain
							}
							meta={ counts?.[ item.id ] }
						/>
					) ) }
				</ul>
				{ editor && (
					<div className="lw-admin-sidenav__group">
						<h2
							className="lw-admin-sidenav__heading"
							id={ `${ navId }-editor` }
						>
							{ editor.title || __( '(no title)', 'lw-slider' ) }
						</h2>
						<ul aria-labelledby={ `${ navId }-editor` }>
							{ sections.map( ( item ) => (
								<NavItem
									key={ item.id }
									item={ item }
									isCurrent={
										route.view === 'editor' &&
										route.tab === item.id
									}
									meta={ editor.meta?.[ item.id ] }
								/>
							) ) }
						</ul>
					</div>
				) }
			</nav>
			<div className="lw-admin-sidebar__foot">
				<a
					className="lw-admin-sidenav__item"
					href={ DOCS_URL }
					target="_blank"
					rel="noopener noreferrer"
				>
					<Icon icon={ help } size={ 20 } />
					<span className="lw-admin-sidenav__label">
						{ __( 'Documentation', 'lw-slider' ) }
					</span>
					<Icon icon={ external } size={ 16 } />
					<span className="screen-reader-text">
						{ __( '(opens in a new tab)', 'lw-slider' ) }
					</span>
				</a>
			</div>
		</aside>
	);
}
