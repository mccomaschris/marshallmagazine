# HerdLine Custom Blocks

All blocks are registered via ACF and follow the same pattern: a `block.json` config, a `callback.php` that builds Timber context, and a Twig template in `views/blocks/`. ACF field configurations are version-controlled in `acf-json/`.

---

## Hero (`acf/hero`)

Full-screen hero section for story pages, typically placed at the top of a post.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `hero_image` | Image | Min width: 1800px |
| `photo_credit` | Text | |
| `hide_post_title` | True/False | |
| `preheading` | Text | |
| `subheading` | Text | |

**Features:**
- Background image with focal point positioning and gradient overlay
- Detects story type from post meta (student/staff/faculty/alum) and renders a decorative divider with spotlight/feature label
- Shows post author when available
- Scroll-triggered entry animation via `data-animate`
- Responsive typography (3rem → 4rem)

---

## Basic Content (`acf/basic-content`)

The most flexible block — rich text, graphics, and callout notes with configurable backgrounds and side images.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `background_style` | Select | `color`, `image`, `pattern` |
| `background_color` | Select | `white`, `green`, `black` |
| `background_image` | Image | Shown when style = image |
| `background_image_opacity` | Range | 0–100, default 15 |
| `pattern` | Select | topleft, topright, bottomleft, bottomright, middlevs, graph, texture, topography, mountain |
| `content_side` | Select | `left`, `center`, `right` |
| `side_fixed_images` | Repeater (max 4) | Only when content_side ≠ center |
| `graphic_side_image` | True/False | |
| `block_content` | Flexible Content | See layouts below |

**`block_content` Layouts:**
- **Text** — WYSIWYG editor
- **Graphics** — decorative SVG graphic (squiggles, slashes, dots, arrows in green/white/black)
- **Note** — callout box with title and basic WYSIWYG copy

**Features:**
- Desktop layout: 4-column grid with 2+2 split (content + sticky side images)
- Background image uses grayscale filter with fixed attachment
- Gradient overlay added when content is off-center over an image background
- Side images rotate ±3° for a playful look

---

## Quote (`acf/quote`)

Large pull quote with flexible placement and background options.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `quote_placement` | Select | `center`, `left`, `right` |
| `quote` | WYSIWYG | Basic toolbar |
| `attribution` | Text | |
| `sub_attribution` | Text | |
| `background` | Select | `color`, `image` |
| `background_color` | Select | `green`, `black`, `white` (when background = color) |
| `background_image` | Image | When background = image |
| `add_overlay_to_image` | True/False | Green overlay over image |
| `quote_vertical_placement` | Select | `center`, `topcenter`, `bottomcenter` (image mode only) |

**Features:**
- Full viewport height (`100svh - 70px`)
- Decorative SVG quotation marks — color varies by background
- Quote placement controls horizontal position and padding
- Responsive typography (2xl → 4xl)

---

## Feature Stories (`acf/feature-stories`)

Full-screen carousel of selected story posts, driven by their hero block data.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `stories` | Post Object | Multiple pages, returns object |

**Features:**
- Scans each selected post's block content to extract `hero_image` and `subheading` from its `acf/hero` block
- Full-height slides with background image, gradient overlay, and focal point positioning
- Each slide shows title, subheading, and a "Read More" CTA
- Down-arrow scroll indicator between slides

---

## More Stories (`acf/more-stories`)

3-column card grid of selected story posts.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `stories` | Post Object | Multiple pages, returns object |

**Features:**
- Same hero block extraction logic as Feature Stories
- Pulls `story_type` post meta for badge label (Student Spotlight, Staff Spotlight, Faculty Feature, Alum Spotlight)
- Responsive grid: 1 col mobile → 3 cols desktop
- Hover effects: drop shadow + underline on title

---

## Photos (`acf/photos`)

Photo gallery with two display modes: polaroid strip or full-screen photo breaks.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `display_type` | Select | `full`, `polaroid` |
| `fade_from_grayscale_to_color` | True/False | Full mode only |
| `photos` | Repeater | |
| ↳ `photo` | Image | Max 1MB |
| ↳ `cover_text` | Text | |
| ↳ `text_placement` | Select | `center`, `topcenter`, `bottomcenter` (when cover_text set) |
| ↳ `photo_credit` | Text | |

**Polaroid mode:**
- Horizontally scrolling strip on a green background
- Images rotate ±3° with hover straightening effect
- Drop shadows and overlapping negative margins

**Full mode:**
- Full-height photo breaks (`100svh - 70px`) with snap scrolling on mobile
- Optional cover text overlay with configurable placement
- Grayscale → color fade animation on scroll (Alpine.js intersection observer)
- Photo credit bottom-right

---

## Recent Issues (`acf/recent-issues`)

Grid of past magazine issue links with cover images.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `recent_issues` | Repeater | |
| ↳ `link` | Link | Array format (url, title, target) |
| ↳ `cover_image` | Image | Max 1MB; jpg, jpeg, png, webp, avif |

**Features:**
- Responsive 3-column grid
- Hover underline on title
- External link support via `target` attribute
- Optimized image srcsets (768w, 1024w)
