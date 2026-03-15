=== BB Content Types ===
Contributors: coffeemugger
Tags: custom post types, taxonomies, rewrites, admin
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create custom WP post types and taxonomies in minutes, without writing code.

== Description ==
BB Content Types helps you create and manage custom post types and taxonomies from a friendly admin screen. Set up labels, slugs, visibility, and editor features without editing code, and keep URLs predictable with clear rewrite controls. It is built for teams that want a reliable, repeatable setup that can be moved between environments.

== Installation ==
1. Upload the `bb-content-types` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Go to "Content Types" in the admin menu to create post types and taxonomies.

== Frequently Asked Questions ==
= Who is this plugin for? =
Anyone who needs custom post types and taxonomies without touching code, including marketers and content teams.

= Will it change my theme or content? =
No. It only registers post types, taxonomies, and rewrite rules. Your theme controls how content is displayed.

= Do I need to flush rewrites? =
Rewrite changes are flagged and flushed automatically for admins on the next load. You can also use the "Flush rewrites" button if needed.

= Does parent mapping affect archives? =
Yes. If a parent page is selected, archive URLs are mapped under the parent path as well.

= Can I export or import configurations? =
Yes. The admin includes import and export tools so you can move settings between sites.

== Screenshots ==
1. Plugin activated with the Content Types admin menu available under the WordPress sidebar.
2. Post Types list screen showing registered content types, status, archive/REST visibility, and quick actions.
3. Edit Post Type screen with label, slug, icon, description, and editor feature configuration.
4. Advanced post type settings for visibility/access, REST behavior, and URL/rewrite configuration.

== Changelog ==
= 1.0.1 =
* Security: Add nonce verification when displaying redirect messages (`bb_ct_message`) in admin notices. Ensures `$_GET['bb_ct_message']` is only shown after a valid nonce check and capability check (WP.org review fix).

= 1.0.2 =
* Added Screenshots

= 1.0.3 =
* Corrected the icon svg

= 1.0.4 =
* Align with Tag version

= 1.0.0 =
* Initial release.

== Upgrade Notice ==
= 1.0.1 =
Security update: nonce and permission checks for admin redirect messages. Recommended upgrade.

= 1.0.0 =
Initial release.
