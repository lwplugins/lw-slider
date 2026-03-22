# LW Slider - LW Site Manager Abilities

LW Slider registers abilities with [LW Site Manager](https://github.com/lwplugins/lw-site-manager) when it is active. These abilities allow AI agents and REST API clients to inspect sliders programmatically.

## Category

`slider` - Slider management abilities

## Abilities

### `lw-slider/list-sliders`

List all sliders registered on the site.

- **Type:** readonly
- **Permission:** `can_manage_options`

**Input:** none (empty object)

**Output:**

```json
{
  "success": true,
  "sliders": [
    {
      "id": 42,
      "title": "Hero Slider",
      "status": "publish",
      "slide_count": 3,
      "shortcode": "[lw_slider id=\"42\"]"
    }
  ]
}
```

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Slider post ID |
| `title` | string | Slider title |
| `status` | string | Post status (`publish` or `draft`) |
| `slide_count` | integer | Number of slides in this slider |
| `shortcode` | string | Ready-to-use shortcode |

---

### `lw-slider/get-slider`

Get full details for a single slider including all slides and settings.

- **Type:** readonly
- **Permission:** `can_manage_options`

**Input:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | integer | yes | Slider post ID |

**Output:**

```json
{
  "success": true,
  "slider": {
    "id": 42,
    "title": "Hero Slider",
    "status": "publish",
    "settings": {
      "min_height_desktop": "400",
      "min_height_mobile": "280",
      "dots": true,
      "arrows": true,
      "arrows_mobile": false,
      "autoplay": false,
      "autoplay_delay": 5000,
      "transition": "slide",
      "loop": true,
      "content_align_h": "center",
      "content_align_v": "center",
      "use_default_styles": true,
      "custom_class": "",
      "swipe": true,
      "keyboard": true,
      "pause_on_hover": false,
      "hide_on_mobile": false
    },
    "slides": [
      {
        "title": "",
        "active": true,
        "bg_type": "image",
        "bg_image_id": 123,
        "bg_color": "#f0f0f0",
        "bg_position": "center center",
        "overlay_color": "",
        "overlay_opacity": 50,
        "headline": "Welcome",
        "subheadline": "Subtitle here",
        "description": "",
        "link_url": "https://example.com",
        "link_target": "_self",
        "cta_mode": "full_slide",
        "button_text": "",
        "image_alt": ""
      }
    ]
  }
}
```

**Slider Settings Fields:**

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `min_height_desktop` | string | `"400"` | Minimum height in px on desktop |
| `min_height_mobile` | string | `"280"` | Minimum height in px on mobile |
| `dots` | boolean | `true` | Show navigation dots |
| `arrows` | boolean | `true` | Show navigation arrows on desktop |
| `arrows_mobile` | boolean | `false` | Show navigation arrows on mobile |
| `autoplay` | boolean | `false` | Enable autoplay |
| `autoplay_delay` | integer | `5000` | Autoplay delay in milliseconds (1000-30000) |
| `transition` | string | `"slide"` | Transition type: `slide` or `fade` |
| `loop` | boolean | `true` | Loop slides infinitely |
| `content_align_h` | string | `"center"` | Horizontal content alignment: `left`, `center`, `right` |
| `content_align_v` | string | `"center"` | Vertical content alignment: `top`, `center`, `bottom` |
| `use_default_styles` | boolean | `true` | Include bundled frontend CSS |
| `custom_class` | string | `""` | Additional CSS class for the slider wrapper |
| `swipe` | boolean | `true` | Enable touch/swipe navigation |
| `keyboard` | boolean | `true` | Enable keyboard navigation |
| `pause_on_hover` | boolean | `false` | Pause autoplay on mouse hover |
| `hide_on_mobile` | boolean | `false` | Hide the slider on mobile devices |

**Slide Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `title` | string | Internal slide label (not displayed) |
| `active` | boolean | Whether the slide is visible |
| `bg_type` | string | Background type: `image` or `color` |
| `bg_image_id` | integer | Attachment ID for background image |
| `bg_color` | string | Hex color for solid background |
| `bg_position` | string | CSS background-position value |
| `overlay_color` | string | Hex color for overlay (empty = none) |
| `overlay_opacity` | integer | Overlay opacity 0-100 |
| `headline` | string | Main headline text |
| `subheadline` | string | Secondary headline text |
| `description` | string | Body paragraph text |
| `link_url` | string | URL for slide link or button |
| `link_target` | string | Link target: `_self` or `_blank` |
| `cta_mode` | string | CTA behavior: `full_slide` or `button` |
| `button_text` | string | Button label (used when `cta_mode` is `button`) |
| `image_alt` | string | Alt text for the background image |

## Error Responses

All abilities return a `WP_Error` on failure:

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `missing_id` | 400 | The `id` parameter was not provided |
| `not_found` | 404 | No slider exists with the given ID |

## Integration Code

The integration is located in `includes/SiteManager/`:

```
includes/SiteManager/
├── Integration.php      # Hooks into lw_site_manager_register_* actions
├── SliderAbilities.php  # Ability definitions (schema, labels, metadata)
└── SliderService.php    # Ability execution callbacks
```
