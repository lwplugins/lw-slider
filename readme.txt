=== LW Slider ===
Contributors: lwplugins
Tags: slider, carousel, responsive, lightweight
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 1.0.1
Requires PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight responsive slider for WordPress. Powered by Splide.js.

== Description ==

Create beautiful, fast, responsive sliders without the bloat.

= Features =

* Custom Post Type based slider management
* Drag & drop slide ordering
* Background image or color per slide
* Content overlay with headline, subheadline, description
* Call-to-action: full slide link or button
* Splide.js powered — lightweight and accessible
* Shortcode: `[lw_slider id="123"]`
* Gutenberg block
* Responsive: separate mobile/desktop min-height
* Autoplay, loop, fade/slide transitions
* Keyboard navigation and swipe support
* Accessibility: prefers-reduced-motion support
* Duplicate slider with one click
* No bloat, no upsell, no tracking

Part of [LW Plugins](https://lwplugins.com) - lightweight WordPress plugins.

== Installation ==

1. Upload to `/wp-content/plugins/lw-slider/`
2. Activate the plugin
3. Go to LW Plugins → Sliders → Add New

Or: `composer require lwplugins/lw-slider`

== Frequently Asked Questions ==

= How do I display a slider? =

Use the shortcode `[lw_slider id="123"]` or the Gutenberg block.

= What JavaScript library is used? =

Splide.js — a lightweight, accessible slider library (~30KB).

== Changelog ==

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
