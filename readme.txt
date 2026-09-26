=== LW Slider ===
Contributors: lwplugins
Tags: slider, carousel, responsive, lightweight
Requires at least: 6.6
Tested up to: 7.1
Stable tag: 1.1.0
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight responsive slider for WordPress. Powered by Splide.js.

== Description ==

Create beautiful, fast, responsive sliders without the bloat.

= Features =

* Slider manager under LW Plugins > Sliders: list, trash, duplicate, and an editor with Slides, Settings and Embed sections
* Drag & drop slide ordering, with Move up / Move down buttons for keyboard users
* Background image (from the media library, with focus point and alt text) or color per slide
* Headline, subheadline, description, optional color overlay
* Call-to-action: full slide link or button
* Splide.js powered, lightweight and accessible: pause/play button for autoplay, arrow keys only while the slider has focus, translated control labels
* Responsive images (srcset, lazy loading after the first slide)
* Shortcode: `[lw_slider id="123"]`
* Gutenberg block with per-block overrides, wide/full alignment
* Separate desktop and mobile minimum height
* Autoplay, loop, fade/slide transitions, swipe
* prefers-reduced-motion support
* No bloat, no upsell, no tracking

Part of [LW Plugins](https://lwplugins.com) - lightweight WordPress plugins.

== Installation ==

1. Upload to `/wp-content/plugins/lw-slider/`
2. Activate the plugin
3. Go to LW Plugins > Sliders and click New slider

Or: `composer require lwplugins/lw-slider`

== Frequently Asked Questions ==

= How do I display a slider? =

Use the shortcode `[lw_slider id="123"]` or the Gutenberg block.

= Which screen width counts as mobile? =

The mobile minimum height and "Hide on mobile" apply to screens up to 768 px wide. The heights are the CSS custom properties `--lw-slider-min-height` and `--lw-slider-min-height-mobile` on the slider element, so a theme can override them.

= Why does my slider not show up? =

Only published sliders without a password are shown. Check the slider's status under Settings.

= What JavaScript library is used? =

Splide.js — a lightweight, accessible slider library (~30KB).

== Changelog ==

= 1.1.0 =
* New: New slider manager under LW Plugins > Sliders: a list with search, status filter, trash (with Undo) and duplicate, and an editor with Slides, Settings and Embed sections. Save with the button or Cmd/Ctrl+S; unsaved changes are kept and you are asked before leaving.
* New: Slides can be reordered by drag and drop or with Move up / Move down buttons (keyboard friendly); slides are duplicated as full copies.
* New: An explicit Overlay switch per slide. New slides start without an overlay; existing overlays are kept.
* New: Autoplaying sliders get a pause/play button (WCAG 2.2.2) and always pause while the slider has keyboard focus. New sliders also pause on hover by default.
* New: Slide images now use the Alt text field and load responsive sizes (srcset). The first slide's image loads right away, the others lazily.
* New: The carousel is announced with the slider's name, and its controls are translatable.
* New: Hungarian translation (informal) for the whole plugin, including the block.
* New: If the slider changed in another window, saving is refused instead of overwriting it, and you can reload the latest version.
* Security: Removed an unused slide-reorder AJAX endpoint that let any author rewrite (and drop) the slides of any slider.
* Security: Duplicating a slider now requires permission to edit that slider, so drafts of other users can no longer be copied.
* Security: Sliders are no longer listed through the core REST API (/wp/v2/lw-slider), which showed published slider titles to visitors who were not logged in.
* Security: Slider data is sanitized on every save path (registered meta with a sanitizer and an edit permission check), and colors, positions and sizes are validated again when the slider is displayed, so no CSS can be injected through them.
* Fix: The mobile minimum height now applies on screens up to 768px wide.
* Fix: New-tab links print a valid rel="noopener" attribute.
* Fix: The block's wide/full alignment, extra CSS class and anchor now reach the page.
* Fix: The same slider shown twice on a page no longer produces duplicate HTML ids.
* Fix: The arrow keys only move a slider while it has focus, not while typing in a form elsewhere on the page.
* Fix: Password-protected sliders are no longer shown on the site.
* Fix: Duplicating a slider no longer fails silently, and backslashes in slide texts survive duplication.
* Fix: Uninstall now also removes trashed sliders, on every site of a multisite network.
* Fix: The LW Plugins overview's fallback link to LW Slider points to the right screen.
* Fix: The block editor script and styles refresh after plugin updates.
* Change: The classic slider list and edit screens open the new slider manager (old links and bookmarks still work).
* Change: The slider stylesheet loads in the page head where a slider is used, avoiding a flash of unstyled slides.
* Change: Requires PHP 8.0 (was 8.2) and WordPress 6.6 (was 6.0).
* Change: Slide backgrounds are now <img class="lw-slider__image"> elements instead of a CSS background on the slide. Custom CSS aimed at the old slide background (for example background-size or background-position on .splide__slide) may need updating.
* Change: The slider manager checks every field when you save and shows problems next to the field. Slide texts are limited to 500 characters (descriptions to 5000); texts and links saved with earlier versions are kept as they are, even if longer, and only new or edited values are checked. Links accept every protocol WordPress allows in links (https:, mailto:, tel:, sms:, ftp:, ...).

= 1.0.10 =
* Fix: notices from themes and other plugins (for example a theme's purchase-code or recommended-plugins notice) could show on the LW Slider screen. They are now kept off every LW Plugins screen, whatever their markup.

= 1.0.9 =
* Fix: the release package and Composer dist no longer ship tests, docs or development configuration

= 1.0.8 =
* Update: Tested up to WordPress 7.1.

= 1.0.7 =
* New: LW Site Manager integration - slider abilities for AI agents
* New: lw-slider/list-sliders - list all sliders
* New: lw-slider/get-slider - get slider details with slides

= 1.0.6 =
* Fix: Smarter autoloader fallback - supports root Composer dependency installs

= 1.0.5 =
* Fix: Graceful error when autoloader is missing (admin notice instead of fatal error)

= 1.0.4 =
* Add hide on mobile option

= 1.0.3 =
* Fix slider height — min-height now passes through full Splide element chain

= 1.0.2 =
* Content area inherits min-height from slider for full coverage

= 1.0.1 =
* Gutenberg block with slider selector and per-block override settings
* Slides now fill full width and height even without link/content
* Slide duplication support
* Fixed Splide.js theme CSS loading

= 1.0.0 =
* Initial release
* CPT-based slider management
* Drag & drop slide ordering
* Splide.js frontend rendering
* Shortcode and Gutenberg block support
