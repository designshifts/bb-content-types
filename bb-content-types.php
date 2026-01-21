<?php
/**
 * Plugin Name: BB Content Types
 * Description: Manage custom post types, taxonomies, and rewrite rules via admin UI.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package BB_Content_Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BB_CT_VERSION', '0.1.0' );
define( 'BB_CT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BB_CT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BB_CT_CONFIG_OPTION', 'bb_content_types_config' );
define( 'BB_CT_OPTION_KEY', BB_CT_CONFIG_OPTION );
define( 'BB_CT_NEEDS_FLUSH_OPTION', 'bb_content_types_needs_flush' );
define( 'BB_CT_MENU_SLUG', 'bb-content-types' );

require_once BB_CT_PLUGIN_DIR . 'inc/config.php';
require_once BB_CT_PLUGIN_DIR . 'inc/validators.php';
require_once BB_CT_PLUGIN_DIR . 'inc/register.php';
require_once BB_CT_PLUGIN_DIR . 'inc/rewrites.php';
require_once BB_CT_PLUGIN_DIR . 'admin/admin.php';
require_once BB_CT_PLUGIN_DIR . 'admin/post-types.php';
require_once BB_CT_PLUGIN_DIR . 'admin/taxonomies.php';
require_once BB_CT_PLUGIN_DIR . 'admin/import-export.php';

register_activation_hook( __FILE__, 'bb_ct_activate' );
register_deactivation_hook( __FILE__, 'bb_ct_deactivate' );

/**
 * Activation hook.
 *
 * @return void
 */
function bb_ct_activate(): void {
	bb_ct_mark_needs_flush();
}

/**
 * Deactivation hook.
 *
 * @return void
 */
function bb_ct_deactivate(): void {
	bb_ct_mark_needs_flush();
}
