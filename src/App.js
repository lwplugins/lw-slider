/**
 * WordPress dependencies
 */
import {
	Button,
	// Core has no stable ConfirmDialog yet (same as the sibling LW admins).
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalConfirmDialog as ConfirmDialog,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon, caution, plus } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import Notices from './components/Notices';
import StatusBadge from './components/StatusBadge';
import { statuses } from './data/labels';
import useSliderList from './data/useSliderList';
import useSliderStore from './data/useSliderStore';
import EditorScreen from './screens/editor/Editor';
import SliderList from './screens/list/SliderList';
import Footer from './shell/Footer';
import SideNav from './shell/SideNav';
import TopBar from './shell/TopBar';
import useRoute from './shell/useRoute';
import useSaveShortcut from './shell/useSaveShortcut';
import useUnsavedWarning from './shell/useUnsavedWarning';

const flag = (
	<span className="lw-admin-navflag">
		<Icon icon={ caution } size={ 18 } />
		<span className="screen-reader-text">
			{ __( 'Has invalid fields', 'lw-slider' ) }
		</span>
	</span>
);

/**
 * Shell + routes: the slider list (Sliders, Trash, the New dialog) and the
 * editor of one slider (Slides, Settings, Embed). Leaving a slider with
 * unsaved changes asks first; Save (top bar or Cmd/Ctrl+S) writes every
 * change of the slider in one request.
 */
export default function App() {
	const list = useSliderList();
	const [ editorId, setEditorId ] = useState( null );
	const store = useSliderStore( editorId );
	const [ blocked, setBlocked ] = useState( null );

	const { route, go } = useRoute( {
		shouldBlock: ( next ) =>
			store.hasEdits &&
			! ( next.view === 'editor' && next.id === store.id ),
		onBlocked: setBlocked,
	} );

	const inEditor = route.view === 'editor';

	useEffect( () => {
		setEditorId( inEditor ? route.id : null );
		if ( ! inEditor ) {
			list.reload();
		}
		// Reload the list whenever it is shown again; list.reload is stable.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ inEditor, route.id ] );

	useUnsavedWarning( store.hasEdits );
	useSaveShortcut(
		() => store.save(),
		store.hasEdits && ! store.isSaving,
		inEditor
	);

	const counts = list.isLoading
		? null
		: {
				sliders: list.items.filter( ( row ) => row.status !== 'trash' )
					.length,
				trash: list.items.filter( ( row ) => row.status === 'trash' )
					.length,
			};

	const errorKeys = Object.keys( store.errors );
	const editor =
		inEditor && store.draft
			? {
					id: store.id,
					title: store.draft.title,
					meta: {
						slides: errorKeys.some( ( key ) =>
							key.startsWith( 'slides' )
						)
							? flag
							: null,
						settings: errorKeys.some(
							( key ) => ! key.startsWith( 'slides' )
						)
							? flag
							: null,
					},
				}
			: inEditor && { id: route.id, title: '' };

	let top;
	let content;

	if ( inEditor ) {
		const status = store.draft ? statuses()[ store.server.status ] : null;
		top = (
			<TopBar
				title={
					store.draft
						? store.draft.title || __( '(no title)', 'lw-slider' )
						: __( 'Slider', 'lw-slider' )
				}
				badge={
					status && (
						<StatusBadge status={ status.tone }>
							{ status.label }
						</StatusBadge>
					)
				}
				store={ store.draft ? store : null }
			/>
		);
		content = (
			<EditorScreen
				store={ store }
				tab={ route.tab }
				go={ go }
				onGone={ list.reload }
			/>
		);
	} else {
		const trash = route.view === 'trash';
		top = (
			<TopBar
				title={
					trash
						? __( 'Trash', 'lw-slider' )
						: __( 'Sliders', 'lw-slider' )
				}
				actions={
					<Button
						__next40pxDefaultSize
						variant="primary"
						icon={ plus }
						href="#new"
					>
						{ __( 'New slider', 'lw-slider' ) }
					</Button>
				}
				store={ null }
			/>
		);
		content = (
			<SliderList
				list={ list }
				trash={ trash }
				creating={ route.view === 'new' }
				go={ go }
			/>
		);
	}

	return (
		<>
			<div className="lw-admin-shell">
				<SideNav
					route={ route }
					editor={ editor || null }
					counts={ counts }
				/>
				<div className="lw-admin-main">
					{ top }
					<main className="lw-admin-scroll">
						<div className="lw-admin-content">{ content }</div>
					</main>
					<Footer />
				</div>
			</div>
			<ConfirmDialog
				isOpen={ !! blocked }
				confirmButtonText={ __( 'Discard changes', 'lw-slider' ) }
				cancelButtonText={ __( 'Keep editing', 'lw-slider' ) }
				onConfirm={ () => {
					const hash = blocked;
					setBlocked( null );
					store.discard();
					go( hash );
				} }
				onCancel={ () => setBlocked( null ) }
			>
				{ __(
					'This slider has unsaved changes. Leave it and discard them?',
					'lw-slider'
				) }
			</ConfirmDialog>
			<Notices />
		</>
	);
}
