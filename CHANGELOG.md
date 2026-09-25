# Changelog

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
