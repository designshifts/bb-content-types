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
		.bb-ct-page-intro{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px;}
		.bb-ct-intro-text{margin:0;color:#606b76;max-width:720px;}
		.bb-ct-card{background:#fff;border:1px solid #dcdcde;border-radius:10px;margin:16px 0;box-shadow:0 1px 2px rgba(0,0,0,.03);}
		.bb-ct-card-header{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 18px;border-bottom:1px solid #eef1f4;}
		.bb-ct-card-header h2{margin:0;font-size:16px;}
		.bb-ct-card-sub{margin:4px 0 0;color:#6b7280;font-size:12px;}
		.bb-ct-table{border:0;margin:0;}
		.bb-ct-table th, .bb-ct-table td{padding:12px 14px;vertical-align:middle;}
		.bb-ct-pill{display:inline-flex;align-items:center;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600;}
		.bb-ct-pill--active{background:#d1fae5;color:#065f46;}
		.bb-ct-pill--inactive{background:#e5e7eb;color:#6b7280;}
		.bb-ct-chip{display:inline-flex;align-items:center;background:#f3f4f6;color:#374151;border-radius:999px;padding:2px 8px;font-size:11px;margin-right:6px;}
		.bb-ct-accordion{border:0;}
		.bb-ct-accordion-summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 18px;}
		.bb-ct-accordion-summary::-webkit-details-marker{display:none;}
		.bb-ct-accordion-icon{width:22px;height:22px;border-radius:999px;background:#f3f4f6;position:relative;}
		.bb-ct-accordion-icon:before{content:"+";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#111827;font-weight:700;}
		.bb-ct-accordion[open] .bb-ct-accordion-icon:before{content:"–";}
		.bb-ct-accordion-body{padding:0 18px 18px;border-top:1px solid #eef1f4;}
		.bb-ct-form-section{padding:16px 0;border-bottom:1px solid #eef1f4;}
		.bb-ct-form-section:last-of-type{border-bottom:0;}
		.bb-ct-form-section h3{margin:0 0 12px;font-size:14px;}
		.bb-ct-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;}
		.bb-ct-field label{display:block;font-weight:600;margin-bottom:6px;}
		.bb-ct-field input[type="text"], .bb-ct-field select, .bb-ct-field textarea{width:100%;max-width:100%;}
		.bb-ct-field textarea{min-height:90px;}
		.bb-ct-field-inline{display:flex;flex-direction:column;justify-content:center;}
		.bb-ct-checkbox-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px 16px;}
		.bb-ct-toggle-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;}
		.bb-ct-toggle-card{border:1px dashed #e5e7eb;border-radius:10px;padding:12px 14px;background:#fafafa;}
		.bb-ct-toggle-card label{font-weight:600;display:block;margin-bottom:6px;}
		.bb-ct-warning{color:#b32d2e;}
		.bb-ct-form-footer{display:flex;justify-content:flex-end;gap:10px;padding-top:16px;}
		@media (max-width: 960px){
			.bb-ct-page-intro{flex-direction:column;align-items:flex-start;}
			.bb-ct-form-grid{grid-template-columns:1fr;}
			.bb-ct-toggle-grid{grid-template-columns:1fr;}
			.bb-ct-checkbox-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
		}
		@media (max-width: 640px){
			.bb-ct-checkbox-grid{grid-template-columns:1fr;}
		}
	';
	wp_register_style( 'bb-content-types-admin', false, array(), BB_CT_VERSION );
	wp_enqueue_style( 'bb-content-types-admin' );
	wp_add_inline_style( 'bb-content-types-admin', $css );

	wp_register_script( 'bb-content-types-admin', '', array(), BB_CT_VERSION, true );
	wp_enqueue_script( 'bb-content-types-admin' );
	wp_add_inline_script(
		'bb-content-types-admin',
		'document.addEventListener("DOMContentLoaded",function(){var trigger=document.getElementById("bb-ct-add-new-post-type");if(!trigger){return;}var details=document.querySelector("#bb-ct-post-type-form .bb-ct-accordion");trigger.addEventListener("click",function(event){if(!details){return;}event.preventDefault();details.open=true;details.scrollIntoView({behavior:"smooth",block:"start"});});});'
	);
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

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['bb_ct_message'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
