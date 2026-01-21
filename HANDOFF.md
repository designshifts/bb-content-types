# BB Content Types — Handoff

This document tracks what was built in the base plugin, what still needs tightening, and a clear Pro add-on spec. It also includes ready-to-paste UI copy.

## 1) Base plugin review — missing / tightened

### A) Placeholders / bootstrap
- [x] Searched for `...` placeholders in plugin PHP files.
- [x] Ensured plugin bootstrap includes module files and activation hooks.
- [ ] Add a dev/CI check that fails if `...` appears in plugin PHP files.

### B) Constants + module loading
- [x] Constants defined (`BB_CT_VERSION`, `BB_CT_CONFIG_OPTION`, `BB_CT_OPTION_KEY`, `BB_CT_NEEDS_FLUSH_OPTION`, `BB_CT_MENU_SLUG`).
- [x] Required modules loaded in `bb-content-types.php`.
- [x] Activation/deactivation hooks mark rewrites for flush.

### C) Rewrite flushing
- [x] Admin-only flush-once handler via `admin_init`.
- [x] Admin notice when a flush is needed.
- [x] Manual “Flush rewrites” button (nonce protected).

### D) Conflict detection
- [x] Checks for reserved slugs.
- [x] Checks against existing posts/pages by path.
- [x] Checks against registered taxonomies.
- [x] Checks against CPT/tax slugs in config.
- [ ] Add more explicit taxonomy base conflicts (e.g., term base settings if used).

### E) Parent Page mapping
- [x] `post_type_link` filter applies parent mapping to single URLs.
- [x] Rewrite rule added for mapped parent paths.
- [x] UI note clarifying mapping affects singles only.
- [x] Archives remain at default unless user sets archive slug.

### F) Import / Export
- [x] Export JSON.
- [x] Import preview with counts and warnings.
- [x] Apply import as a separate, nonce-protected action.
- [x] Single rewrite flush flag set after import apply.

### G) REST / meta policy
- [x] No unauthenticated endpoints.
- [x] No REST exposure of internal config.
- [ ] If/when meta is registered, ensure `register_post_meta` with `auth_callback`.

### H) UX completeness
- [x] CPT list table includes status + actions.
- [x] Duplicate + disable for CPTs (soft disable).
- [x] Duplicate for taxonomies.
- [x] Delete confirmation warns content remains.

## 2) Pro add-on handoff — detailed spec

Create **BB Content Types Pro** as an add-on that depends on the free plugin.

### 2.1 Field Groups (native “ACF-lite”)
**What**  
Allow admins to define meta fields per CPT with a simple UI.

**Field types**  
text, textarea, url, number, boolean, select, date (optional)

**Requirements**
- Stored in config under `field_groups`
- Rendered in block editor sidebar panel (`PluginDocumentSettingPanel`)
- Registers meta via `register_post_meta`:
  - `show_in_rest: true`
  - strict `sanitize_callback`
  - `auth_callback` tied to edit capability for the CPT
- Field “lock” option to make meta read-only for editors

**Why Pro**  
Avoids ACF dependency, adds high-value modeling while staying native.

### 2.2 Advanced permalink tokens
**What**  
Define permalink structures like:
- `/%department%/%postname%/`
- `/%location%/%postname%/`

**Requirements**
- UI token selector (only taxonomies attached to the CPT + postname)
- Rewrite generation + resolver
- Fallback if no term exists
- Conflict warnings

### 2.3 Multisite / Network Mode
**What**  
Central model managed once, pushed to subsites.

**Capabilities**
- Network-level registry
- Push to selected sites
- Per-site override toggle
- Diff view for divergence
- Safe rewrite flush per site (queued/flagged)

### 2.4 Governance & permissions
**What**  
Separate “who can change the model” from “who can publish content”.

**Features**
- Custom caps: `bb_ct_manage_models`, `bb_ct_publish_models`
- Optional approval flow for model changes:
  - Draft changes
  - Review → Apply

### 2.5 Environment lock (production safety)
**What**  
Disable model edits unless a constant is set:
- `BB_CT_ALLOW_MODEL_EDITS=true`

### 2.6 Redirect assistant for slug changes
**What**  
Offer to create 301 rules when slugs/archives change.

**Implementation**
- Basic redirect store or integration via filter

### 2.7 Audit log
**What**  
Track who changed the model and when.

**Data**
- timestamp, user, action, before/after summary, warnings

### 2.8 WP-CLI commands
**Commands**
- `bbct export`
- `bbct import --file=...`
- `bbct validate`
- `bbct flush-rewrites`

### Pro milestones (build order)
1. Pro bootstrap + dependency check on Free
2. Field Groups UI + meta registration
3. Advanced permalink tokens
4. Governance (caps + approvals)
5. Environment lock
6. Redirect assistant
7. Audit log
8. WP-CLI
9. Multisite mode

## 3) UI copy (labels + help text + notices)

### Top-level page
**Title:** Content Types  
**Intro:** Define custom post types, taxonomies, and URL behavior with predictable settings that can be safely handed off.

### Post Types list (table)
**Empty state title:** No content types yet  
**Empty state body:** Add a custom post type to model your site content (Careers, Case Studies, Docs, Events). You can adjust URLs and attach taxonomies later.  
**Primary button:** Add Post Type

**Columns**
- Name
- Slug
- Status
- Archive
- REST API
- URL Base
- Attached Taxonomies
- Actions

**Status tooltip**  
Disabled types are not registered, but existing content remains in the database.

### Post Type editor fields
**Plural label**  
Help: “Shown in the admin menu and list screens.”

**Singular label**  
Help: “Used for editor labels and buttons.”

**Slug**  
Help: “Lowercase, letters/numbers/hyphens only. Used in URLs and as the internal post type key.”

**Description**  
Help: “Optional. Helps teams understand what this content type is for.”

**Menu icon**  
Help: “Dashicon used in the WordPress admin menu.”

**Supports**  
Help: “Choose which editor features are enabled for this content type.”

**Visibility**
- Public: “If disabled, content is admin-only and not publicly queryable.”
- Show in REST API: “Required for block editor and headless use cases.”
- Show in Admin Menu: “If disabled, content is still accessible but not shown in the left menu.”
- Exclude from search: “Prevents content from appearing in site search results.”

**URL & rewrites**
- Rewrite base: “The base path used for single URLs. Default is the slug.”
- With front: “If enabled, WordPress will prefix URLs with your site’s permalink front base.”
- Has archive: “Enable an archive page for this content type.”
- Archive slug: “Optional. Defaults to the rewrite base.”

**Parent Page mapping**
- Parent page: “Prepends the selected page path to single URLs (example: /company/careers/job-title/).”
- Mapping note: “Parent mapping affects single URLs only. If you change this later, consider adding redirects.”

### Taxonomies tab
**Empty state title:** No taxonomies yet  
**Body:** Taxonomies classify content across one or more content types (Department, Location, Topic). Create once, then attach where needed.

**Taxonomy fields**
- Plural label / Singular label
- Slug
- Hierarchical (Category-style)
- Show in REST API
- Show in admin columns
- Attach to content types

**Attach help**  
A taxonomy must be attached to at least one content type to be used.

### Import/Export tab copy
**Export configuration**  
Download a JSON file containing your post types and taxonomies. Useful for backups and moving between environments.

**Import configuration**  
Import a JSON configuration file. You’ll see a preview of changes before anything is applied.

### Notices
**Rewrite updated notice**  
Rewrite rules updated.

**Needs flush notice**  
URL settings changed. Rewrite rules will be refreshed automatically. If you notice 404s, use “Flush rewrites” below.

**Conflict warning (inline)**  
This slug may conflict with an existing page or another content type. Saving could cause URL collisions.

**Disable confirmation**  
Disable this content type? Existing content will remain in the database but won’t be registered until re-enabled.
