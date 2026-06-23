# HerdLine Custom Blocks

All blocks are registered via ACF and follow the same pattern: a `block.json` config, a `callback.php` that builds Timber context, and a Twig template in `views/blocks/`. ACF field configurations are version-controlled in `acf-json/`.

---

## Hero (`acf/hero`)

Hero for story pages, placed at the top of a post. Has **two modes** driven by `minimal_hero`:

- **Editorial (default, `minimal_hero` off)** — full-viewport, full-bleed. Type anchored bottom-left in a constrained column over a darkened image. For features.
- **Minimal (`minimal_hero` on)** — compact "Portrait split" for Q&As / short stories. Contained 4:5 portrait on the left, text on the right, over a brand **green gradient** (`bg-gradient-to-br from-green-dark via-green-darker to-green-darkest`) so it reads distinctly above the always-white Q&A block. Stacks image-first on mobile.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `minimal_hero` | True/False | Switches to the compact Portrait-split layout |
| `hero_image` | Image | Min width: 1800px (the portrait in minimal mode) |
| `photo_credit` | Text | Editorial: bottom-right. Minimal: below the portrait |
| `hide_post_title` | True/False | |
| `preheading` | Text | Used as the kicker when no story type is set |
| `subheading` | Text | |
| `more_contrast` | True/False | Editorial only — darkens the gradient overlay for busy images |
| `student_details` | Repeater (`detail_label` + `detail_value`) | Minimal only — label/value rows (e.g. Major, Hometown, Graduating). Shown only when `minimal_hero` is on |

**Shared behavior:**
- Kicker (tracked uppercase green label) derived from post-meta story type — Student/Staff/Alumni Spotlight or Faculty Feature — falling back to `preheading`, then the parent edition title
- **Crimson Pro serif** headline, `mundial` subheading, `new-science` byline (post-meta `author`)

**Editorial mode:**
- Background image with focal-point positioning and a directional `bg-gradient-to-tr` overlay (intensified when `more_contrast` is on)
- Scroll-triggered entry animation via `data-animate` with staggered delays

**Minimal mode:**
- Top padding clears the fixed 70px masthead; portrait carries a soft shadow + hairline ring against the green
- Mirrors the editorial hierarchy: inline-rule **edition/eyebrow** kicker → serif title → larger subheading
- The story type renders as a standout **white badge** (e.g. "Alumni Spotlight") below the subheading, instead of a kicker; no publish date or byline
- Optional **student-detail rows** (`student_details`) render under the badge as hairline-divided label/value pairs — small uppercase green-bright label over a white value

**Persistent article chrome (in `views/layouts/base.twig`, not the block):**
- A 3px brand-green **reading-progress bar** tied to scroll depth, pinned to the bottom of the fixed header
- The **article title reveals** once the hero has scrolled away (`scrollY > viewport − 140px`); suppressed on edition homepages
- Responsive placement: on desktop the title fades in centered within the 70px masthead; on mobile (where the wordmark fills that row) it drops into a slim full-width strip below the masthead, with the progress bar along the strip's bottom edge
- The back-to-issue link is a single tap-target row (chevron + edition title) with an `aria-label`
- Driven by the `articleChrome` Alpine component (passive `scroll`/`resize` listeners)

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
