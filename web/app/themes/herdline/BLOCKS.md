# HerdLine Custom Blocks

All blocks are registered via ACF and follow the same pattern: a `block.json` config, a `callback.php` that builds Timber context, and a Twig template in `views/blocks/`. ACF field configurations are version-controlled in `acf-json/`.

---

## Hero (`acf/hero`)

Hero for story pages, placed at the top of a post. Has **four modes** driven by `hero_type`:

- **Feature (default, `hero_type` = `feature`)** — full-viewport, full-bleed. Type anchored bottom-left in a constrained column over a darkened image. For features.
- **Minimal (`hero_type` = `minimal`)** — compact "Portrait split" for Q&As / short stories. Contained 4:5 portrait on the right, text on the left, over a brand **green gradient** (`bg-gradient-to-br from-green-dark via-green-darker to-green-darkest`) so it reads distinctly above the always-white Q&A block. Stacks image-first on mobile.
- **Edition (`hero_type` = `edition`)** — edition homepage hero with a full-bleed landscape image, centered text overlay, and a "Scroll to enter" arrow.
- **No Hero (`hero_type` = `none`)** — renders nothing on the front end (the `#content` skip-link anchor still renders). For cover-led pages whose first block already provides its own cover, e.g. the Gallery block. In the editor a dashed placeholder keeps the locked hero block visible and selectable. Pair with Page Meta's `hide_title_bar` so the scroll-reveal title stays off too.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `hero_type` | Button Group | `feature`, `minimal`, `edition`, `none` (No Hero) |
| `hero_image` | Image | Min width: 1800px |
| `photo_credit` | Text | Feature/Edition: bottom-right. Minimal: below the portrait |
| `hide_post_title` | True/False | |
| `preheading` | Text | Used as the kicker when no story type is set |
| `subheading` | Text | |
| `more_contrast` | True/False | Feature only — darkens the gradient overlay for busy images |
| `student_details` | Repeater (`detail_label` + `detail_value`) | Minimal only — label/value rows (e.g. Major, Hometown, Graduating) |

**Shared behavior:**
- Kicker (tracked uppercase green label) derived from post-meta `story_type` — Student/Staff/Athletics/Alumni Spotlight, Faculty Feature, Board of Governors Feature, or Moment in Marshall History — falling back to `preheading`, then the parent edition title
- **Crimson Pro serif** headline, `mundial` subheading, `new-science` byline (post-meta `author`)

**Feature mode:**
- Full-viewport (`h-svh`) background image with focal-point positioning and a directional `bg-gradient-to-tr` overlay (intensified when `more_contrast` is on)
- Scroll-triggered entry animation via `data-animate` with staggered delays

**Minimal mode:**
- Top padding clears the fixed 70px masthead; portrait carries a soft shadow + hairline ring against the green
- The story type renders as a standout **white badge** below the subheading rather than a kicker
- Optional **student-detail rows** (`student_details`) render under the badge as hairline-divided label/value pairs — small uppercase green-bright label over a white value

**Edition mode:**
- Cinematic aspect ratio (`lg:h-[68vh]`) with centered bottom-anchored type overlay
- Two-tone serif title: all words except the last in white, last word in `text-green-bright`
- Hairline rule + subheading beneath the title; "Scroll to enter" chevron arrow

**Persistent article chrome (in `views/layouts/base.twig`, not the block):**
- A 3px brand-green **reading-progress bar** tied to scroll depth, pinned to the bottom of the fixed header
- The **article title reveals** once the hero has scrolled away (`scrollY > viewport − 140px`); suppressed on edition homepages
- Responsive placement: on desktop the title fades in centered within the 70px masthead; on mobile it drops into a slim full-width strip below the masthead with the progress bar along its bottom edge
- The back-to-issue link is a single tap-target row (chevron + edition title)
- Driven by the `articleChrome` Alpine component (passive `scroll`/`resize` listeners)

---

## Basic Content (`acf/basic-content`)

The most flexible block — rich text, graphics, pull quotes, callout notes, inset images, separators, feature links, and YouTube video embeds with configurable backgrounds and side layouts.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `background_style` | Select | `color`, `image`, `pattern` |
| `background_color` | Select | `white`, `green`, `black` |
| `background_image` | Image | When style = image |
| `background_image_opacity` | Range | 0–100, default 15 |
| `pattern` | Select | Full-background: `topleft`, `topright`, `bottomleft`, `bottomright`, `middlevs`, `graph`, `texture`, `topography`, `mountain`. Side-fill: `side_dots`, `side_diagonal`, `side_grid`, `side_vertical`, `side_crosshatch`, `side_rings` |
| `content_side` | Select | `left`, `center`, `right` (default `left`) |
| `side_fixed_images` | Repeater (max 4) | **Legacy — do not use for new stories.** Only when `content_side` ≠ `center` |
| `graphic_side_image` | True/False | Treats side images as graphics (no frame, drop shadow instead) |
| `block_content` | Flexible Content | See layouts below |

**`block_content` Layouts:**
| Layout | Fields | Notes |
|---|---|---|
| **Text** | `content` (WYSIWYG), `lede` (True/False) | Reading-column body copy; `lede` applies a larger intro-paragraph style |
| **Heading** | `heading` (Text), `level` (Select: `h2`/`h3`) | `h2` carries the editorial green kicker rule; `h3` is a plain subheading. Top margin reset when it leads the block |
| **Graphics** | `graphic_type` (Select: `pull_quote`, `callout`, `stat`, `divider`) | Routes to `partials/graphics.twig`; currently only `pull_quote` type is fully implemented |
| **Inset Image** | `image` (Image) | Breakout image (`lg:-mr-24`) with camera-icon brow rule, 7:2 desktop grid with caption in right column, and parallax scroll via `insetImageParallax` Alpine component. Rendered via shared `partials/inset-image.twig` (also used by the Q&A block) |
| **Pull Quote** | `quote` (Textarea), `attribution` (Text), `sub_attribution` (Text) | Inline pull quote with rounded panel, serif quote text, and `font-science` attribution |
| **Separator** | `style` (Select: `asterism`, `center_rule`, `dots`, `diagonal`, `grid`) | Quiet break between layouts. Does **not** support the `photo` style (that's standalone separator only). Patterns are CSS-only and radial-faded |
| **Note** | `title` (Text), `note_copy` (WYSIWYG) | Callout box with title and basic WYSIWYG copy |
| **YouTube Video** | `video_id` (Text), `overline` (Text), `caption` (Text), `credit` (Text) | Facade pattern: poster thumbnail + green play button render server-side; iframe injected on click via Alpine (`youtube-nocookie.com`, `autoplay=1&rel=0`). Breakout width (`lg:w-[calc(100%+200px)]`). Overline renders above in large `font-science`; caption and credit below as a `<figcaption>` |
| **Feature Links** | `heading` (Text, optional), `links` (Repeater of `link` (Link) + `description` (Text, optional)) | Standalone set of featured "read more" links, distinct from inline body links. Rendered via `partials/feature-links.twig`: optional kicker heading above a responsive 2-up grid of bordered, tinted cards, each with title, optional description, and a hover arrow. Colors adapt to the block background |

**Pattern behavior:**
- **Full-background patterns** (`middlevs` and all non-`side_*` options) trigger `fill_viewport` mode (`min-h-[calc(100svh-70px)]`) so the pattern fills the screen
- **Side-fill patterns** (`side_*`) paint a faint texture in the empty column opposite the content (desktop only). Ink color adapts automatically for green/black blocks. A soft directional mask fades the pattern toward the copy column. On mobile a narrow strip appears at the trailing edge instead
- `middlevs` injects an inline `<style>` for `background-attachment: fixed` to achieve a parallax effect

**Layout:**
- Desktop: 4-column grid, 2+2 split between content and side images
- Background image uses `grayscale` filter with `bg-fixed` attachment; directional gradient overlay added when content is off-center
- Content column placement uses explicit grid lines so the left edge is consistent across `left` and `center` modes; `right` mirrors to the far column

---

## Quote (`acf/quote`)

Large pull quote with flexible placement and background options.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `quote_placement` | Select | `center`, `left`, `right` |
| `quote` | WYSIWYG | Basic toolbar |
| `attribution` | Text | |
| `sub_attribution` | Text | Shown in a smaller weight below attribution |
| `background` | Select | `color`, `image` |
| `background_color` | Select | `green`, `black`, `white` (when background = color) |
| `background_image` | Image | When background = image |
| `add_overlay_to_image` | True/False | Green gradient scrim over image |
| `quote_vertical_placement` | Select | `center`, `topcenter`, `bottomcenter` (image mode only) |
| `fit_content` | True/False | When true: sized to content (`py-24 lg:py-36`). When false (default): full viewport height (`100svh - 70px`) |

**Features:**
- Decorative oversized serif quotation mark — color varies by background (`text-green-darkest/80` on green, `text-green-bright` on black, `text-green-darkest/20` on white)
- Attribution flanked by hairline rules with centered flex layout
- `font-science` uppercase tracking for attribution and sub-attribution

---

## Separator (`acf/separator`)

A break between stacked blocks so the transition reads intentionally instead of as a gap of whitespace.

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
- **Drone photo + overlay** — `45svh`→`55svh` full-bleed photo with a green-to-dark scrim and an optional bottom-left label (`green-bright` rule + uppercase `font-science`)

The three pattern styles are pure CSS and masked with a soft radial fade so they concentrate in the center rather than hard-cutting at the edges.

---

## Feature Stories (`acf/feature-stories`)

Full-screen carousel of selected story posts, driven by their hero block data.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `stories` | Post Object | Multiple pages, returns object |

**Features:**
- Callback scans each selected post's block content to extract `hero_image` and `subheading` from its `acf/hero` block
- Full-height slides with background image, gradient overlay, and focal-point positioning
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
- Pulls `story_type` post meta for story type label (Student Spotlight, Staff Spotlight, Athletics Spotlight, Alumni Spotlight, Faculty Feature, Board of Governors Feature, Moment in Marshall History)
- Responsive grid: 1 col mobile → 3 cols desktop
- Hover effects: drop shadow + underline on title

---

## Photos (`acf/photos`)

Photo gallery with three display modes: polaroid strip, full-screen photo breaks, or scroll strip.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `display_type` | Select | `polaroid`, `scroll`; anything else (including unset) renders full-screen photo break mode |
| `background_color` | Select | `white`, `green`, `gray`, `black` — polaroid mode only |
| `fade_from_grayscale_to_color` | True/False | Full mode only |
| `photos` | Repeater | |
| ↳ `photo` | Image | Max 1MB |
| ↳ `cover_text` | Text | |
| ↳ `photo_credit` | Text | |

**Polaroid mode (`display_type = polaroid`):**
- Horizontally scrolling strip on a configurable background color
- Images rotate ±3° with staggered offset by loop index (`index % 3`); hover straightens
- Drop shadows and overlapping negative margins

**Full-screen mode (default / unset `display_type`):**
- Full-height photo breaks (`100svh - 70px`) with snap scrolling on mobile
- Optional cover text overlay
- Grayscale → color fade animation via `x-intersect.once.half` Alpine directive (when `fade_from_grayscale_to_color` is on)
- Photo credit bottom-right

**Scroll mode (`display_type = scroll`):**
- Continuous `snap-x snap-mandatory` horizontal scrolling strip

---

## Gallery (`acf/gallery`)

Full-bleed, mobile-first photo essay — the online counterpart to a printed photo spread. An optional title cover, then one full-height frame per photo with a context tag + caption over a gradient scrim.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `eyebrow` | Text | Small label above the cover title (optional) |
| `background` | Select | `dark` (default), `white`, `green` — ground behind cover/gaps; captions always sit on a dark scrim |
| `title` | Text | Cover title, heavy uppercase (`font-sans`) |
| `script_word` | Text | Single word set in `font-serif` italic accent (stand-in for a brush script) |
| `dek` | Textarea | One-line intro under the title |
| `photos` | Repeater | Each row becomes a full-height frame |
| ↳ `photo` | Image | Required; jpg, jpeg, png, webp, avif |
| ↳ `tag` | Text | Short context label, e.g. "Game Day" |
| ↳ `caption` | Text | One-sentence description |
| ↳ `credit` | Text | Photo credit |

**Features:**
- Cover section renders only when `title`/`eyebrow`/`script_word` is set; includes an animated scroll cue
- Frames are `100svh - 70px` with `snap-start`; images use focal-point `object-position` and 768/1024/1600w srcsets
- Caption tag uses `green-bright` accent rule; caption + credit overlay a bottom gradient scrim
- Overlaps intentionally with Photos' full-screen mode; Gallery adds the title cover and per-photo tag label

---

## Gallery Highlight (`acf/gallery-highlight`)

A call-to-action for the issue homepage inviting readers into that issue's Family Scrapbook (the Gallery page of reader-submitted photos). "Editorial Mosaic" layout: a text/photo split — eyebrow rule, headline, serif accent, body and button on the left; an asymmetric photo mosaic on the right.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `background` | Select | `white` (default), `green`, `black` |
| `eyebrow` | Text | Small label above the headline, e.g. "Family Scrapbook" (optional) |
| `headline` | Text | Required; the main statement (`font-sans`, heavy) |
| `script_word` | Text | Optional `font-serif` italic accent beneath the headline |
| `body` | Textarea | 1–2 sentences of supporting copy (optional) |
| `button` | Link | Links to the gallery page; also powers the "View all" mosaic tile |
| `photos` | Gallery | Teaser photos; first three fill the mosaic (tall lead tile + one square + overlay) |

**Features:**
- Mosaic is a tall lead tile (`3/4`, vertically centered) plus two squares; the third tile is a `+N / View all` overlay linking to `button`
- `+N` count = `photos count − 3` when more than three are added, otherwise falls back to "more"
- Missing photos degrade to branded green/brown/dark gradient placeholder tiles (with a camera glyph), so the block previews before images are added
- Colour tokens (headline, eyebrow, rule, accent, button variant) adapt to `background`

---

## Recent Issues (`acf/recent-issues`)

"Cover Wall" — a uniform portrait grid of every magazine issue. Print editions
show their designed cover; online-only issues use a representative photo (campus,
a shot from the story, etc.) in the same footprint. Each tile carries a badge
marking it "Print" or "Online".

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `recent_issues` | Repeater | |
| ↳ `issue_date` | Date Picker | Return `Ymd`; used to sort issues newest-first. Undated rows sink to the bottom |
| ↳ `link` | Link | Array format (url, title, target) |
| ↳ `cover_image` | Image | Max 1MB; jpg, jpeg, png, webp, avif. Print cover, or any representative photo for online-only issues |
| ↳ `online_only` | True/False | UI toggle (default off). Drives the Print/Online badge — this is the explicit source of truth, **not** the presence of a cover image |
| `background_image` | Image | Optional; block-level. Rendered behind the grid under an 85% white scrim |

**Features:**
- Responsive 2-up / 3-up portrait grid; covers cropped `object-top` (focal-point aware)
- Issues auto-sorted newest-first by `issue_date` (sorted in `callback.php`)
- Print vs. Online badge driven by the `online_only` toggle (green "Online" / black "Print")
- Rows missing a `cover_image` entirely fall back to a brand-green typographic tile (wordmark + season/year)
- Issue name derived from the link title (`"Read Fall 2025 Issue"` → `"Fall 2025"`)
- Hover lift + cover zoom, animated "Read" affordance
- Optional dimmed background image behind the section
- External link support via `target` attribute; optimized image srcsets (768w, 1024w)

---

## Q and A (`acf/q-a`)

Question-and-answer block for interview-style stories. Renders a flexible list of Q&A pairs and pull quotes from a single `rows` flexible content field.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `rows` | Flexible Content | See layouts below |

**`rows` Layouts:**
| Layout | Fields | Notes |
|---|---|---|
| **Question / Answer** (`question_answer`) | `question` (Text), `answer` (WYSIWYG) | Question in small-caps gray, answer in large `font-sans` body text. Hairline top border separates rows, suppressed when the previous layout was a pull quote or inset image |
| **Pull Quote** (`pull_quote`) | `quote` (Text), `attribution` (Text) | Left-bordered `bg-green/5` panel that bleeds past the container margins (`-mx-8 lg:-mx-12`). Attribution in small-caps green |
| **Inset Image** (`inset_image`) | `image` (Image) | Shared with Basic Content — renders `partials/inset-image.twig` constrained to the reading column width (`max-w-[60ch]`). No hairline borders above or below; the image's own spacing carries the separation |

**Features:**
- `animate-group` wrapper triggers scroll animations on all child rows
- Q&A answers support inline links (`text-green underline`) and paragraph spacing via `[&_p]` selectors

---

## Table of Contents (`acf/toc`)

Edition table of contents with an intro label, issue label, and grouped story sections.

**Fields:**
| Field | Type | Notes |
|---|---|---|
| `intro_label` | Text | Default: "Contents" |
| `issue_label` | Text | Falls back to the page title |
| `sections` | Repeater | |
| ↳ `section_label` | Text | |
| ↳ `stories` | Post Object | Multiple pages |
| ↳ `basic_grid` | True/False | Force a uniform 3-up card grid for this section |

**Layout adaptation (per section, unless `basic_grid` is on):**
| Story count | Layout |
|---|---|
| 1 | Full-width `feature` tile (4:3 → 16:9 → 21:9 aspect) |
| 2 | 2-column card grid |
| 3 | 3-column card grid |
| 4 | 2-column card grid |
| 5+ | Full-width `feature` tile (first story) + card grid (remaining) |

**Featured sections:**
- Sections whose label is `"featured"` or `"features"` (case-insensitive) bump the type scale inside both `card` and `feature` macros (`prominent = true`)

**Tile anatomy:**
- Full-bleed image with gradient scrim (`from-black/85`), focal-point positioning, slow zoom on hover
- Kicker (`font-science`, `text-green-bright`), serif title, dek (prefers `description` over `subheading`), `font-science` byline
- Fallback: green gradient when no hero image

**Callback:**
- Builds `sections` array via `herdline_toc_story_hero()` helper, which extracts `hero_image`, `subheading`, `description`, `author`, and `story_type` from each post's ACF hero block

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
- Media column is `sticky` on desktop; text steps scroll against it
- Media frames cross-fade via opacity transitions as the active step changes (driven by IntersectionObserver)
- Videos auto-play when their step enters the viewport and pause when it leaves
- Respects `prefers-reduced-motion`: transitions are suppressed when the user has reduced motion enabled
- Each step has a `min-h-[60vh]` to ensure enough scroll distance for the media to dwell

---

## Page Meta (not a block)

`group_68efc742a204c.json` is attached to the `page` post type and contains editorial metadata:

| Field | Type | Notes |
|---|---|---|
| `story_type` | Select | Student Spotlight, Staff Spotlight, Athletics Spotlight, Alum Spotlight, Faculty Feature, Board of Governors Feature, Moment in Marshall History, Other |
| `author` | Text | |
| `edition_homepage` | True/False | |
| `hide_link_back_to_edition` | True/False | |
| `include_related` | True/False | |
| `description` | Textarea | Used on the table of contents |
| `hide_reading_status` | True/False | |
| `hide_title_bar` | True/False | Hides the page title that reveals under the masthead on scroll (the `articleChrome` reveal in `base.twig`). For cover-led pages where the first block already carries the title. On mobile this also removes the reading-progress bar, which rides inside the title strip; desktop progress is unaffected |

These fields are used by the Hero, Feature Stories, More Stories, and Table of Contents blocks for badge labels, bylines, kickers, and edition navigation.
