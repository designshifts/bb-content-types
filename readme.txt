=== BB Content Types ===
Contributors: chrisandersondesigns
Tags: custom post types, taxonomies, rewrites, admin
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage custom post types, taxonomies, and rewrite rules via a clean admin UI.

== Description ==
BB Content Types provides a structured interface for defining custom post types and taxonomies with predictable rewrite behavior. It is designed for teams who want a consistent, repeatable configuration that can be safely handed off between environments.

== Installation ==
1. Upload the `bb-content-types` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Go to "Content Types" in the admin menu to create post types and taxonomies.

== Frequently Asked Questions ==
= Do I need to flush rewrites? =
The plugin will flag rewrite changes and flush automatically for admins on the next load. You can also use the "Flush rewrites" button if needed.

= Does parent mapping affect archives? =
If a parent page is selected, archive URLs are mapped under the parent path as well.

== Changelog ==
= 0.1.0 =
* Initial release.

== Upgrade Notice ==
= 0.1.0 =
Initial release.
