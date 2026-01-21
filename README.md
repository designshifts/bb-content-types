# BB Content Types

Define custom post types, taxonomies, and rewrite rules via a clean admin UI.

## Setup
1. Activate **BB Content Types**.
2. Go to **Better Builds -> Content Types**.
3. Add Post Types and Taxonomies.
4. Save changes and confirm rewrite notice.

## Post Types
Fields:
- Plural, Singular, Slug
- Description, Icon
- Supports (Title, Editor, Excerpt, Thumbnail, Revisions, Custom Fields, Page Attributes)
- Visibility toggles (Public, Archive, Hierarchical, REST, Admin Menu, Exclude from search)
- Rewrite Base, Archive slug, With front
- Parent Page Mapping (optional)

## Taxonomies
Fields:
- Plural, Singular, Slug
- Hierarchical
- Show in REST
- Show in admin columns
- Attach to post types

## Rewrite Behavior
- Rewrites are flushed only when settings change (admin-only).
- Parent Page mapping prepends the selected page path to single URLs.
- Manual “Flush rewrites” button available for recovery.

## Conflict Checks
Slugs are validated against:
- Reserved slugs (`wp`, `admin`, `api`, `tag`, `category`, etc.)
- Existing posts/pages
- Registered taxonomies
- Other CPT/tax slugs in config

## Import/Export
- Export downloads JSON with `{ post_types, taxonomies }`.
- Import expects the same structure and includes a preview step before apply.

## Security
- Admin actions require `manage_options`.
- Nonces used on all mutations.
- Strict slug sanitization and validation.
