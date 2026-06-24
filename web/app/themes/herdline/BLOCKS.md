# HerdLine Custom Blocks

All blocks are registered via ACF and follow the same pattern: a `block.json` config, a `callback.php` that builds Timber context, and a Twig template in `views/blocks/`. ACF field configurations are version-controlled in `acf-json/`.

---

## Hero (`acf/hero`)

Hero for story pages, placed at the top of a post. Has **three modes** driven by `hero_type`:

- **Feature (default, `hero_type` = `feature`)** — full-viewport, full-bleed. Type anchored bottom-left in a constrained column over a darkened image. For features.
- **Minimal (`hero_type` = `minimal`)** — compact "Portrait split" for Q&As / short stories. Contained 4:5 portrait on the left, text on the right, over a brand **green gradient** (`bg-gradient-to-br from-green-dark via-green-darker to-green-darkest`) so it reads distinctly above the always-white Q&A block. Stacks image-first on mobile.
- **Edition (`hero_type` = `edition`)** — edition homepage hero with a wider landscape image and centered text.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `hero_type` | Button Group | `feature`, `minimal`, `edition` |
| `hero_image` | Image | Min width: 1800px |
| `photo_credit` | Text | Editorial: bottom-right. Minimal: below the portrait |
| `hide_post_title` | True/False | |
| `preheading` | Text | Used as the kicker when no story type is set |
| `subheading` | Text | |
| `more_contrast` | True/False | Feature only — darkens the gradient overlay for busy images |
| `student_details` | Repeater (`detail_label` + `detail_value`) | Minimal only — label/value rows (e.g. Major, Hometown, Graduating). Shown only when `hero_type` is `minimal` |

**Shared behavior:**
- Kicker (tracked uppercase green label) derived from post-meta story type — Student/Staff/Alumni Spotlight or Faculty Feature — falling back to `preheading`, then the parent edition title
- **Crimson Pro serif** headline, `mundial` subheading, `new-science` byline (post-meta `author`)

**Feature mode:**
- Background image with focal-point positioning and a directional `bg-gradient-to-tr` overlay (intensified when `more_contrast` is on)
- Scroll-triggered entry animation via `data-animate` with staggered delays

**Minimal mode:**
- Top padding clears the fixed 70px masthead; portrait carries a soft shadow + hairline ring against the green
- Mirrors the editorial hierarchy: inline-rule **edition/eyebrow** kicker → serif title → larger subheading
- The story type renders as a standout **white badge** (e.g. "Alumni Spotlight") below the subheading, instead of a kicker; no publish date or byline
- Optional **student-detail rows** (`student_details`) render under the badge as hairline-divided label/value pairs — small uppercase green-bright label over a white value

**Edition mode:**
- Wider landscape image with centered text overlay
- No student details, no byline

**Persistent article chrome (in `views/layouts/base.twig`, not the block):**
- A 3px brand-green **reading-progress bar** tied to scroll depth, pinned to the bottom of the fixed header
- The **article title reveals** once the hero has scrolled away (`scrollY > viewport − 140px`); suppressed on edition homepages
- Responsive placement: on desktop the title fades in centered within the 70px masthead; on mobile (where the wordmark fills that row) it drops into a slim full-width strip below the masthead, with the progress bar along the strip's bottom edge
- The back-to-issue link is a single tap-target row (chevron + edition title) with an `aria-label`
- Driven by the `articleChrome` Alpine component (passive `scroll`/`resize` listeners)

---

## Basic Content (`acf/basic-content`)

The most flexible block — rich text, graphics, pull quotes, callout notes, inset images, and separators with configurable backgrounds.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `background_style` | Select | `color`, `image`, `pattern` |
| `background_color` | Select | `white`, `green`, `black` |
| `background_image` | Image | Shown when style = image |
| `background_image_opacity` | Range | 0–100, default 15 |
| `pattern` | Select | Corner/tile: `topleft`, `topright`, `bottomleft`, `bottomright`, `middlevs`, `graph`, `texture`, `topography`, `mountain` |
| `content_side` | Select | `left`, `center`, `right` |
| `side_fixed_images` | Repeater (max 4) | **Legacy — do not use for new stories.** Only when `content_side` ≠ `center` |
| `graphic_side_image` | True/False | |
| `block_content` | Flexible Content | See layouts below |

**`block_content` Layouts:**
| Layout | Fields | Notes |
|---|---|---|
| **Text** | `content` (WYSIWYG) | Reading-column body copy |
| **Heading** | `heading` (Text), `level` (Select: `h2`/`h3`) | `h2` carries the editorial green kicker rule; `h3` is a plain subheading. Top margin reset when it leads the block |
| **Graphics** | `graphic_type` (Select: `pull_quote`, `callout`, `stat`, `divider`) | Decorative SVG graphic — squiggles, slashes, dots, arrows in green/white/black |
| **Inset Image** | `image` (Image) | Inline image with a camera-icon brow rule above it, a 7:2 desktop grid with the caption in the right column, and parallax scroll effect |
| **Pull Quote** | `quote` (Textarea), `attribution` (Text), `sub_attribution` (Text) | Inline pull quote with attribution and sub-attribution |
| **Note** | `title` (Text), `note_copy` (WYSIWYG) | Callout box with title and basic WYSIWYG copy |
| **Separator** | `style` (Select: `asterism`, `center_rule`, `dots`, `diagonal`, `grid`) | Quiet break between stacked layouts. No photo option (unlike the standalone `acf/separator` block). Patterns are CSS-only and radial-faded |

**Features:**
- Desktop layout: 4-column grid with 2+2 split (content + sticky side images)
- Background image uses grayscale filter with fixed attachment
- Gradient overlay added when content is off-center over an image background
- Side images rotate ±3° for a playful look
- Layouts are spaced with `mt-12 lg:mt-16` / `mb-14 lg:mb-20` between items

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

## Separator (`acf/separator`)

A break between stacked blocks (e.g. two consecutive Basic Content blocks) so the
transition reads intentionally instead of as a gap of whitespace. Pick a treatment
per-instance with `style`. The neutral options are text-free (the next section's
heading does the labeling); the drone photo is the one "feature" treatment.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `style` | Select | `asterism` (default), `center_rule`, `dots`, `diagonal`, `grid`, `photo` |
| `background_image` | Image | When `style = photo`; full-bleed photo (e.g. a drone shot of Huntington) |
| `label` | Text | When `style = photo`; optional caption, hidden when blank |

**Styles:**
- **Asterism** — scaled-up three-diamond typographic ornament, centered on white
- **Center-mark rule** — full-measure hairline pinned by a bold green diamond
- **Dot grid** — fine green dot field, radial-faded toward the edges
- **Diagonal lines** — fine green pinstripe hatch, radial-faded
- **Grid lines** — fine neutral graph rule, radial-faded
- **Drone photo + overlay** — `45svh`→`55svh` full-bleed photo with a green-to-dark
  scrim and an optional bottom-left label (`green-bright` rule, uppercase `font-science`)

The three pattern styles are pure CSS (crisp at any width) and masked with a soft
radial fade so they concentrate in the center rather than hard-cutting at the edges.

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

3-column card grid of selected story posts. *(Legacy field group — "Block: More Stories (Old)" in ACF.)*

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

Photo gallery with three display modes: polaroid strip, full-screen photo breaks, or scroll strip.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `display_type` | Select | `full`, `polaroid`, `scroll` |
| `background_color` | Select | `white`, `green`, `gray`, `black` — when `display_type == polaroid` |
| `fade_from_grayscale_to_color` | True/False | Full mode only |
| `photos` | Repeater | |
| ↳ `photo` | Image | Max 1MB |
| ↳ `cover_text` | Text | |
| ↳ `photo_credit` | Text | |

**Polaroid mode:**
- Horizontally scrolling strip on a configurable background color
- Images rotate ±3° with hover straightening effect
- Drop shadows and overlapping negative margins

**Full mode:**
- Full-height photo breaks (`100svh - 70px`) with snap scrolling on mobile
- Optional cover text overlay with configurable placement
- Grayscale → color fade animation on scroll (Alpine.js intersection observer)
- Photo credit bottom-right

**Scroll mode:**
- Continuous scrolling strip

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

---

## Q and A (`acf/q-a`)

Question-and-answer block for interview-style stories. A preheading, heading, and subheading sit above a list of Q&A pairs.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `preheading` | Text | |
| `heading` | Text | |
| `subheading` | Text | |
| `questions` | Repeater | |
| ↳ `question` | Text | |
| ↳ `answer` | WYSIWYG | |

**Features:**
- Clean typographic hierarchy with generous spacing
- Questions styled as bold, answers as regular body text
- Responsive layout

---

## Table of Contents (`acf/toc`)

Edition table of contents with an intro label, issue label, and grouped story sections.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `intro_label` | Text | Default: "Contents" |
| `issue_label` | Text | |
| `sections` | Repeater | |
| ↳ `section_label` | Text | |
| ↳ `stories` | Post Object | Multiple pages |
| ↳ `basic_grid` | True/False | Use a basic grid instead of the default layout |

**Features:**
- Responsive grid layout with story cards
- Optional `basic_grid` for a simpler grid presentation

---

## Scrollytelling (`acf/scrollytelling`)

Scroll-driven narrative block with pinned media and step-by-step text panels.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `intro_eyebrow` | Text | |
| `media_side` | Select | `right`, `left` |
| `background_style` | Select | `white`, `green`, `black` |
| `steps` | Repeater | |
| ↳ `media_type` | Select | `image`, `video` |
| ↳ `image` | Image | When `media_type == image` |
| ↳ `video` | File | When `media_type == video` |
| ↳ `caption` | Text | |
| ↳ `step_label` | Text | |
| ↳ `step_heading` | Text | |
| ↳ `step_text` | WYSIWYG | |

**Features:**
- Media (image or video) pins on one side while text steps scroll on the other
- Step-by-step labels, headings, and body text
- Background color adapts to the selected style

---

## Page Meta (not a block)

`group_68efc742a204c.json` is attached to the `page` post type and contains editorial metadata:

| Field | Type | Notes |
|---|---|---|
| `story_type` | Select | Student Spotlight, Staff Spotlight, Alum Spotlight, Faculty Feature |
| `author` | Text | |
| `edition_homepage` | True/False | |
| `hide_link_back_to_edition` | True/False | |
| `include_related` | True/False | |
| `description` | Textarea | |
| `hide_reading_status` | True/False | |

These fields are used by the Hero, Feature Stories, and More Stories blocks for badge labels, bylines, and edition navigation.
