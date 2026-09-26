/**
 * WordPress dependencies
 */
import { SVG, Path } from '@wordpress/primitives';

/**
 * Path data of the LW Slider mark (24x24): a slide frame with previous /
 * next chevrons and pagination dots. `back` is drawn at 40%, `front` solid.
 */
export const MARK = {
	back: 'M4 3h16a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zM7.5 19.8a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4zM16.5 19.8a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4z',
	front: 'M9.7 7.3a1 1 0 0 1 0 1.4L7.9 10.5l1.8 1.8a1 1 0 1 1-1.4 1.4l-2.5-2.5a1 1 0 0 1 0-1.4l2.5-2.5a1 1 0 0 1 1.4 0zM14.3 7.3a1 1 0 0 1 1.4 0l2.5 2.5a1 1 0 0 1 0 1.4l-2.5 2.5a1 1 0 1 1-1.4-1.4l1.8-1.8-1.8-1.8a1 1 0 0 1 0-1.4zM12 19.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z',
};

/**
 * LW Slider mark, inlined. Both layers follow `color`, which the stylesheet
 * sets to the brand token ($lw-brand).
 */
export default function SliderMark() {
	return (
		<SVG
			className="lw-admin-mark"
			viewBox="0 0 24 24"
			xmlns="http://www.w3.org/2000/svg"
			aria-hidden="true"
			focusable="false"
		>
			<Path opacity=".4" fill="currentColor" d={ MARK.back } />
			<Path fill="currentColor" d={ MARK.front } />
		</SVG>
	);
}
