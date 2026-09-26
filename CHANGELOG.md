# Changelog

## [1.1.0] - 2026-09-26

### Added
- New slider manager under LW Plugins > Sliders: a list with search, status filter, trash (with Undo) and duplicate, and an editor with Slides, Settings and Embed sections. Save with the button or Cmd/Ctrl+S; unsaved changes are kept and you are asked before leaving.
- Slides can be reordered by drag and drop or with Move up / Move down buttons (keyboard friendly); slides are duplicated as full copies.
- An explicit Overlay switch per slide. New slides start without an overlay; existing overlays are kept.
- Autoplaying sliders get a pause/play button (WCAG 2.2.2) and always pause while the slider has keyboard focus. New sliders also pause on hover by default.
- Slide images now use the Alt text field and load responsive sizes (srcset). The first slide's image loads right away, the others lazily.
- The carousel is announced with the slider's name, and its controls are translatable.
- Hungarian translation (informal) for the whole plugin, including the block.
- If the slider changed in another window, saving is refused instead of overwriting it, and you can reload the latest version.

### Security
- Removed an unused slide-reorder AJAX endpoint that let any author rewrite (and drop) the slides of any slider.
- Duplicating a slider now requires permission to edit that slider, so drafts of other users can no longer be copied.
- Sliders are no longer listed through the core REST API (/wp/v2/lw-slider), which showed published slider titles to visitors who were not logged in.
- Slider data is sanitized on every save path (registered meta with a sanitizer and an edit permission check), and colors, positions and sizes are validated again when the slider is displayed, so no CSS can be injected through them.

### Fixed
- The mobile minimum height now applies on screens up to 768px wide.
- New-tab links print a valid rel="noopener" attribute.
- The block's wide/full alignment, extra CSS class and anchor now reach the page.
- The same slider shown twice on a page no longer produces duplicate HTML ids.
- The arrow keys only move a slider while it has focus, not while typing in a form elsewhere on the page.
- Password-protected sliders are no longer shown on the site.
- Duplicating a slider no longer fails silently, and backslashes in slide texts survive duplication.
- Uninstall now also removes trashed sliders, on every site of a multisite network.
- The LW Plugins overview's fallback link to LW Slider points to the right screen.
- The block editor script and styles refresh after plugin updates.

### Changed
- The classic slider list and edit screens open the new slider manager (old links and bookmarks still work).
- The slider stylesheet loads in the page head where a slider is used, avoiding a flash of unstyled slides.
- Requires PHP 8.0 (was 8.2) and WordPress 6.6 (was 6.0).
- Slide backgrounds are now <img class="lw-slider__image"> elements instead of a CSS background on the slide. Custom CSS aimed at the old slide background (for example background-size or background-position on .splide__slide) may need updating.
- The slider manager checks every field when you save and shows problems next to the field. Slide texts are limited to 500 characters (descriptions to 5000); texts and links saved with earlier versions are kept as they are, even if longer, and only new or edited values are checked. Links accept every protocol WordPress allows in links (https:, mailto:, tel:, sms:, ftp:, ...).

## [1.0.10] - 2026-09-25

### Fixed
- Notices from themes and other plugins (for example a theme's purchase-code or recommended-plugins notice) could show on the LW Slider screen. They are now kept off every LW Plugins screen, whatever their markup.

## [1.0.9] - 2026-09-06

### Fixed
- The release package and the Composer/Packagist dist no longer ship tests, docs or development configuration (`.gitattributes` export-ignore plus unified release excludes). A hosting malware scanner had flagged a unit-test fixture on a customer site

## [1.0.8] - 2026-08-20

### Changed
- Tested up to WordPress 7.1.

## [1.0.7] - 2026-03-22

### Added
- LW Site Manager integration - slider abilities for AI agents
- `lw-slider/list-sliders` ability - list all sliders
- `lw-slider/get-slider` ability - get slider details with slides

## [1.0.6]

### Fixed
- Smarter autoloader fallback - supports root Composer dependency installs

## [1.0.5]

### Fixed
- Graceful error when autoloader is missing (admin notice instead of fatal error)

## [1.0.4]

### Added
- Hide on mobile option

## [1.0.3]

### Fixed
- Slider height - `min-height` now passes through full Splide element chain

## [1.0.2]

### Changed
- Content area inherits `min-height` from slider for full coverage

## [1.0.1]

### Added
- Gutenberg block with slider selector and per-block override settings
- Slide duplication support

### Fixed
- Slides now fill full width and height even without link/content
- Splide.js theme CSS loading

## [1.0.0]

### Added
- Initial release
- CPT-based slider management
- Drag and drop slide ordering
- Splide.js frontend rendering
- Shortcode and Gutenberg block support
