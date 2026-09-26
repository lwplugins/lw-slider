# LW Slider admin REST API

The React slider manager (`admin.php?page=lw-slider`) talks to
`lw-slider/v1/admin/*`. Cookie auth (`X-WP-Nonce`, added by `apiFetch`) is the
only auth; every route checks a capability. The block keeps using the
unchanged `GET lw-slider/v1/sliders` (published sliders only).

Sliders stay what they were in 1.0: the `lw-slider` post type with the
`_lw_slider_slides` (list of slides) and `_lw_slider_settings` (object) meta.
No data migration.

## Permissions

| Route | Capability |
|---|---|
| list, create | `edit_posts` |
| read, update | `edit_post` on that slider |
| trash, delete, restore | `delete_post` on that slider |
| duplicate | `edit_post` on the source + `edit_posts` |
| set `status: "publish"` | `publish_posts` (field error otherwise) |

A slider ID that does not exist or is another post type answers `404
lw_slider_not_found` (only after `edit_posts`, so other IDs are not probed).

## Errors

`WP_Error` JSON: `{ code, message, data: { status, ... } }`.

| Status | Code | When |
|---|---|---|
| 400 | `lw_slider_invalid` | a field is invalid; `data.fields` = `{ "path": [ messages ] }`, nothing was saved |
| 400 | `lw_slider_not_trashed` | force-delete or restore of a slider not in the trash |
| 404 | `lw_slider_not_found` | no such slider |
| 409 | `lw_slider_conflict` | `modified` is stale; `data.modified` = the current token |
| 413 | `lw_slider_too_large` | body over 1 MB |
| 500 | `lw_slider_save_failed`, `lw_slider_action_failed` | the database write failed |

Field paths: `title`, `status`, `settings`, `settings.{key}`, `slides`,
`slides.{index}`, `slides.{index}.{key}`, or an unknown top-level key.

## Shapes

**List item**

```json
{ "id": 12, "title": "Home", "status": "publish", "modified": "2026-09-26 08:00:00",
  "slides": { "total": 3, "active": 2 }, "thumb": "https://…-150x150.jpg" | null,
  "shortcode": "[lw_slider id=\"12\"]", "can_delete": true }
```

**Slider**

```json
{ "id": 12, "title": "Home", "status": "draft", "modified": "2026-09-26 08:00:00",
  "shortcode": "[lw_slider id=\"12\"]", "can_delete": true,
  "settings": { …17 keys…, "min_height_desktop": 400, "min_height_mobile": 280 },
  "slides": [ { …16 keys… } ],
  "images": { "34": { "id": 34, "missing": false, "thumb": "…", "medium": "…", "alt": "…" } } }
```

`modified` is `post_modified_gmt` and doubles as the concurrency token. Every
successful update moves it forward. Two saves from different windows within
the same second are not told apart (documented limitation).

`images` is an object keyed by attachment ID (an empty array `[]` when no slide
has an image).

## Routes

| Method | Route | Body | Result |
|---|---|---|---|
| GET | `/admin/sliders` | | `{ items: [ item ], meta: { can_publish } }`, every status incl. `trash`, newest change first |
| POST | `/admin/sliders` | `{ title?, status?, settings?, slides? }` | `201` slider. Draft by default; settings = defaults + sent keys |
| GET | `/admin/sliders/{id}` | | slider |
| POST/PUT/PATCH | `/admin/sliders/{id}` | `{ title?, status?, settings?, slides?, modified? }` | slider |
| DELETE | `/admin/sliders/{id}` | | trash → item. `?force=true` on a trashed slider → `{ id, deleted: true }` |
| POST | `/admin/sliders/{id}/restore` | | item. Previous status back (a published one only for `publish_posts` users, else draft) |
| POST | `/admin/sliders/{id}/duplicate` | | `201` slider: a draft "Title (Copy)" with the same slides and settings |

Update rules: only sent keys change. `settings` is merged over the stored
settings (a switch that is not sent is never turned off). `slides` replaces the
stored list (order = array order); a slide may omit keys (defaults fill them).
Unknown keys are errors. After validation the shared `SliderSanitizer` writes
the stored shape (min heights stored as strings, as in 1.0).

## Field rules

Settings (all optional in a request):

| Key | Rule |
|---|---|
| `min_height_desktop`, `min_height_mobile` | integer 100–1200 (digit strings accepted) |
| `autoplay_delay` | integer 1000–30000 (ms) |
| `transition` | `slide` \| `fade` |
| `content_align_h` | `left` \| `center` \| `right` |
| `content_align_v` | `top` \| `center` \| `bottom` |
| `custom_class` | one CSS class name (`sanitize_html_class` leaves it unchanged) or `""` |
| `dots`, `arrows`, `arrows_mobile`, `autoplay`, `loop`, `pause_on_hover`, `swipe`, `keyboard`, `use_default_styles`, `hide_on_mobile` | JSON boolean |

Slides (at most 100):

| Key | Rule |
|---|---|
| `active` | boolean |
| `bg_type` | `image` \| `color` |
| `bg_image_id` | integer ≥ 0 (attachment ID, 0 = none) |
| `bg_color` | `#rgb` / `#rrggbb` |
| `bg_position` | one of the 9 `left top` … `right bottom` |
| `overlay_color` | `""` (no overlay) or `#rgb` / `#rrggbb` |
| `overlay_opacity` | integer 0–100 |
| `link_url` | `""`, http(s)/mailto/tel address, or a path; normalized with `esc_url_raw` |
| `link_target` | `_self` \| `_blank` |
| `cta_mode` | `full_slide` \| `button` |
| `description` | text ≤ 5000 |
| `title`, `headline`, `subheadline`, `button_text`, `image_alt` | text ≤ 500 |

`title` (slide) has no UI; it is kept for compatibility.
