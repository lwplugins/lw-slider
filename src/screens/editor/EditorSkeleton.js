/**
 * Internal dependencies
 */
import {
	SkeletonBlock,
	SkeletonRegion,
	SkeletonRows,
	SkeletonSection,
	SkeletonText,
} from '../../components/skeleton';

/**
 * Placeholder of the editor: the slide list and a form card side by side
 * (settings and embed tabs get form cards).
 *
 * @param {Object} props
 * @param {string} props.tab Editor tab.
 */
export default function EditorSkeleton( { tab } ) {
	if ( tab !== 'slides' ) {
		return (
			<SkeletonRegion className="lw-skel-tab">
				<SkeletonSection>
					<SkeletonRows count={ 3 } />
				</SkeletonSection>
				<SkeletonSection>
					<SkeletonRows count={ 4 } />
				</SkeletonSection>
			</SkeletonRegion>
		);
	}

	return (
		<SkeletonRegion className="lw-slides">
			<SkeletonSection>
				<span className="lw-skel-stack">
					{ [ 0, 1, 2 ].map( ( i ) => (
						<span key={ i } className="lw-skel-slide">
							<SkeletonBlock width={ 48 } height={ 36 } />
							<SkeletonText width="70%" />
						</span>
					) ) }
				</span>
			</SkeletonSection>
			<span className="lw-skel-stack is-loose">
				<SkeletonSection>
					<SkeletonBlock height={ 160 } />
					<SkeletonRows count={ 2 } />
				</SkeletonSection>
				<SkeletonSection>
					<SkeletonRows count={ 3 } />
				</SkeletonSection>
			</span>
		</SkeletonRegion>
	);
}
