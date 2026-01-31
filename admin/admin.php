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
add_action( 'bb_core_register', 'bb_ct_register_core_panel' );
add_action( 'admin_enqueue_scripts', 'bb_ct_enqueue_admin_styles' );

/**
 * Enqueue admin styles for page header.
 *
 * @param string $hook Current admin page.
 * @return void
 */
function bb_ct_enqueue_admin_styles( string $hook ): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$plugin = isset( $_GET['plugin'] ) ? sanitize_key( $_GET['plugin'] ) : '';

	$is_plugin_page = in_array( $page, array( 'bb-content-types', 'bb-content-types-taxonomies', 'bb-content-types-import-export' ), true );
	$is_core_page   = 'bb-core' === $page && 'bb-content-types' === $plugin;

	if ( ! $is_plugin_page && ! $is_core_page ) {
		return;
	}

	$css = '
		.bb-ct-header{display:flex;align-items:center;justify-content:space-between;gap:16px;background:#fff;border-bottom:1px solid #dcdcde;padding:16px 20px;margin:-20px -20px 20px;}
		.bb-ct-header-left{flex:1;min-width:0;}
		.bb-ct-header h1{display:flex;align-items:center;gap:8px;margin:0;font-size:23px;font-weight:400;line-height:1.3;}
		.bb-ct-header-subtitle{margin:6px 0 0;color:#6b7280;font-size:13px;}
	';
	wp_add_inline_style( 'wp-admin', $css );
}

/**
 * Render page header.
 *
 * @param string $title   Page title.
 * @param string $icon    Dashicon name (without 'dashicons-' prefix).
 * @param string $subtitle Optional subtitle.
 * @return void
 */
function bb_ct_render_page_header( string $title, string $icon = 'database', string $subtitle = '' ): void {
	?>
	<div class="bb-ct-header">
		<div class="bb-ct-header-left">
			<h1>
				<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
				<?php echo esc_html( $title ); ?>
			</h1>
			<?php if ( $subtitle ) : ?>
				<p class="bb-ct-header-subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

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
 * Register the plugin in Better Builds Core.
 *
 * @return void
 */
function bb_ct_register_core_panel(): void {
	if ( ! function_exists( 'bb_core_register_plugin' ) ) {
		return;
	}

	bb_core_register_plugin(
		array(
			'slug'  => 'bb-content-types',
			'label' => __( 'Content Types', 'bb-content-types' ),
			'icon'  => 'database',
			'pages' => array(
				'post-types'    => 'bb_ct_render_post_types_page',
				'taxonomies'    => 'bb_ct_render_taxonomies_page',
				'import-export' => 'bb_ct_render_import_export_page',
			),
		)
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
