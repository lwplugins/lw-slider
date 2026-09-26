=== LW Slider ===
Contributors: lwplugins
Tags: slider, carousel, responsive, lightweight
Requires at least: 6.6
Tested up to: 7.1
Stable tag: 1.0.10
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
