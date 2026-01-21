<?php
/**
 * Admin menu and routing.
 *
 * @package BB_Content_Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'bb_ct_register_admin_menu' );
add_action( 'admin_notices', 'bb_ct_admin_notices' );
add_action( 'admin_post_bb_ct_flush_rewrites', 'bb_ct_handle_flush_rewrites' );

/**
 * Register admin menu.
 *
 * @return void
 */
function bb_ct_register_admin_menu(): void {
	add_menu_page(
		__( 'Content Types', 'bb-content-types' ),
		__( 'Content Types', 'bb-content-types' ),
		'manage_options',
		'bb-content-types',
		'bb_ct_render_post_types_page',
		'dashicons-database',
		58
	);

	add_submenu_page(
		'bb-content-types',
		__( 'Post Types', 'bb-content-types' ),
		__( 'Post Types', 'bb-content-types' ),
		'manage_options',
		'bb-content-types',
		'bb_ct_render_post_types_page'
	);

	add_submenu_page(
		'bb-content-types',
		__( 'Taxonomies', 'bb-content-types' ),
		__( 'Taxonomies', 'bb-content-types' ),
		'manage_options',
		'bb-content-types-taxonomies',
		'bb_ct_render_taxonomies_page'
	);

	add_submenu_page(
		'bb-content-types',
		__( 'Import/Export', 'bb-content-types' ),
		__( 'Import/Export', 'bb-content-types' ),
		'manage_options',
		'bb-content-types-import-export',
		'bb_ct_render_import_export_page'
	);
}

/**
 * Admin notices.
 *
 * @return void
 */
function bb_ct_admin_notices(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['bb_ct_message'] ) ) {
		$message = sanitize_text_field( wp_unslash( $_GET['bb_ct_message'] ) );
		printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html( $message ) );
	}

	if ( get_option( BB_CT_NEEDS_FLUSH_OPTION ) ) {
		printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'URL settings changed. Rewrite rules will be refreshed automatically. If you notice 404s, use the flush button below.', 'bb-content-types' ) );
	}
}

/**
 * Handle manual rewrite flush.
 *
 * @return void
 */
function bb_ct_handle_flush_rewrites(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_flush_rewrites' );
	flush_rewrite_rules();
	bb_ct_clear_needs_flush();
	wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types&bb_ct_message=Rewrite%20rules%20updated' ) );
	exit;
}
