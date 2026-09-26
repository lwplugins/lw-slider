/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import LoadError from '../../components/LoadError';
import EditorSkeleton from './EditorSkeleton';
import EmbedTab from './EmbedTab';
import SettingsTab from './SettingsTab';
import SlidesTab from './SlidesTab';

/**
 * The open slider: loading and error states, the conflict notice, and the
 * current tab.
 *
 * @param {Object}                 props
 * @param {Object}                 props.store  Editor store.
 * @param {string}                 props.tab    slides|settings|embed.
 * @param {(hash: string) => void} props.go     Navigate.
 * @param {() => void}             props.onGone The slider left (trash).
 */
export default function Editor( { store, tab, go, onGone } ) {
	if ( store.error ) {
		return <LoadError message={ store.error } onRetry={ store.reload } />;
	}

	if ( store.isLoading || ! store.draft ) {
		return <EditorSkeleton tab={ tab } />;
	}

	return (
		<>
			{ store.conflict && (
				<Notice
					status="warning"
					isDismissible={ false }
					actions={ [
						{
							label: __(
								'Reload the latest version',
								'lw-slider'
							),
							onClick: store.reload,
							variant: 'secondary',
						},
					] }
				>
					{ __(
						'This slider was changed in another window or by someone else. Reloading shows the latest version and drops your unsaved changes here.',
						'lw-slider'
					) }
				</Notice>
			) }
			{ store.server.status === 'trash' && (
				<Notice status="warning" isDismissible={ false }>
					{ __(
						'This slider is in the trash. Restore it from the Trash to show it on the site again.',
						'lw-slider'
					) }
				</Notice>
			) }
			{ tab === 'settings' && <SettingsTab store={ store } /> }
			{ tab === 'embed' && (
				<EmbedTab store={ store } go={ go } onGone={ onGone } />
			) }
			{ tab === 'slides' && <SlidesTab store={ store } /> }
		</>
	);
}
