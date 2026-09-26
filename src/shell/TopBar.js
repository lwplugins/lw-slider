/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { displayShortcut } from '@wordpress/keycodes';

/**
 * Main column header: title (plus an optional badge) left; actions right.
 * With a store: Discard while there are edits, and Save (Cmd/Ctrl+S).
 *
 * @param {Object}      props
 * @param {string}      props.title   Current screen title.
 * @param {Element}     props.badge   Optional badge after the title.
 * @param {Element}     props.actions Optional extra actions (before Save).
 * @param {Object|null} props.store   Editor store ({ hasEdits, isSaving, save, discard }), or null.
 */
export default function TopBar( { title, badge, actions, store } ) {
	return (
		<header className="lw-admin-topbar">
			<div className="lw-admin-topbar__heading">
				<h1 className="lw-admin-topbar__title">{ title }</h1>
				{ badge }
			</div>
			<div className="lw-admin-topbar__actions">
				{ store?.hasEdits && (
					<>
						<span className="lw-admin-topbar__dirty">
							{ __( 'Unsaved changes', 'lw-slider' ) }
						</span>
						<Button
							__next40pxDefaultSize
							variant="tertiary"
							onClick={ store.discard }
						>
							{ __( 'Discard', 'lw-slider' ) }
						</Button>
					</>
				) }
				{ actions }
				{ store && (
					<Button
						__next40pxDefaultSize
						variant="primary"
						className="lw-admin-save"
						isBusy={ store.isSaving }
						disabled={ ! store.hasEdits || store.isSaving }
						accessibleWhenDisabled
						onClick={ () => store.save() }
					>
						<kbd>{ displayShortcut.primary( 's' ) }</kbd>
						{ /* The short label replaces the long one on narrow screens. */ }
						<span className="lw-admin-save__full">
							{ __( 'Save changes', 'lw-slider' ) }
						</span>
						<span className="lw-admin-save__short">
							{ __( 'Save', 'lw-slider' ) }
						</span>
					</Button>
				) }
			</div>
		</header>
	);
}
