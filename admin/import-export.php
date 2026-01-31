<?php
/**
 * Import/Export admin UI.
 *
 * @package BB_Content_Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_bb_ct_export', 'bb_ct_handle_export' );
add_action( 'admin_post_bb_ct_import_preview', 'bb_ct_handle_import_preview' );
add_action( 'admin_post_bb_ct_import_apply', 'bb_ct_handle_import_apply' );

/**
 * Render import/export page.
 *
 * @return void
 */
function bb_ct_render_import_export_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<?php bb_ct_render_page_header( __( 'Import / Export', 'bb-content-types' ), 'upload', __( 'Content Types', 'bb-content-types' ) ); ?>

		<h3><?php esc_html_e( 'Export configuration', 'bb-content-types' ); ?></h3>
		<p><?php esc_html_e( 'Download a JSON file containing your post types and taxonomies. Useful for backups and moving between environments.', 'bb-content-types' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'bb_ct_export' ); ?>
			<input type="hidden" name="action" value="bb_ct_export" />
			<?php submit_button( __( 'Download export', 'bb-content-types' ), 'secondary', 'submit', false ); ?>
		</form>

		<hr />

		<h3><?php esc_html_e( 'Import configuration', 'bb-content-types' ); ?></h3>
		<p><?php esc_html_e( 'Import a JSON configuration file. You’ll see a preview of changes before anything is applied.', 'bb-content-types' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'bb_ct_import_preview' ); ?>
			<input type="hidden" name="action" value="bb_ct_import_preview" />
			<textarea name="import_json" rows="10" class="large-text code"></textarea>
			<?php submit_button( __( 'Preview import', 'bb-content-types' ), 'secondary', 'submit', false ); ?>
		</form>

		<?php
		$preview = bb_ct_get_import_preview();
		if ( $preview ) :
			?>
			<hr />
			<h3><?php esc_html_e( 'Import preview', 'bb-content-types' ); ?></h3>
			<p>
				<?php
				printf(
					esc_html__( 'Will create or update %1$d post types and %2$d taxonomies.', 'bb-content-types' ),
					(int) $preview['counts']['post_types'],
					(int) $preview['counts']['taxonomies']
				);
				?>
			</p>
			<?php if ( ! empty( $preview['warnings'] ) ) : ?>
				<ul>
					<?php foreach ( $preview['warnings'] as $warning ) : ?>
						<li><?php echo esc_html( $warning ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'bb_ct_import_apply' ); ?>
				<input type="hidden" name="action" value="bb_ct_import_apply" />
				<?php submit_button( __( 'Apply import', 'bb-content-types' ), 'primary', 'submit', false ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Handle export.
 *
 * @return void
 */
function bb_ct_handle_export(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_export' );

	$config = bb_ct_get_config();
	$payload = array(
		'post_types' => $config['post_types'],
		'taxonomies' => $config['taxonomies'],
	);

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=bb-content-types.json' );
	echo wp_json_encode( $payload );
	exit;
}

/**
 * Handle import.
 *
 * @return void
 */
function bb_ct_handle_import_preview(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_import_preview' );

	$json = isset( $_POST['import_json'] ) ? wp_unslash( $_POST['import_json'] ) : '';
	$preview = bb_ct_build_import_preview( $json );
	if ( ! $preview ) {
		wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types-import-export&bb_ct_message=Invalid%20JSON' ) );
		exit;
	}

	bb_ct_set_import_preview( $preview );

	wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types-import-export&bb_ct_message=Preview%20ready' ) );
	exit;
}

/**
 * Apply import after preview.
 *
 * @return void
 */
function bb_ct_handle_import_apply(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_import_apply' );

	$preview = bb_ct_get_import_preview();
	if ( ! $preview ) {
		wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types-import-export&bb_ct_message=No%20preview%20found' ) );
		exit;
	}

	$config = bb_ct_get_config();
	$config['post_types'] = $preview['data']['post_types'];
	$config['taxonomies'] = $preview['data']['taxonomies'];
	bb_ct_save_config( $config );
	bb_ct_clear_import_preview();

	wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types-import-export&bb_ct_message=Imported' ) );
	exit;
}

/**
 * Build import preview data.
 *
 * @param string $json JSON payload.
 * @return array|null
 */
function bb_ct_build_import_preview( string $json ): ?array {
	$data = json_decode( $json, true );
	if ( ! is_array( $data ) || ! isset( $data['post_types'], $data['taxonomies'] ) ) {
		return null;
	}
	$post_types = is_array( $data['post_types'] ) ? $data['post_types'] : array();
	$taxonomies = is_array( $data['taxonomies'] ) ? $data['taxonomies'] : array();
	$warnings = array();
	$config = bb_ct_get_config();

	foreach ( array_keys( $post_types ) as $slug ) {
		$warnings = array_merge( $warnings, bb_ct_check_slug_conflicts( $slug, $config ) );
	}
	foreach ( array_keys( $taxonomies ) as $slug ) {
		$warnings = array_merge( $warnings, bb_ct_check_slug_conflicts( $slug, $config ) );
	}

	return array(
		'data' => array(
			'post_types' => $post_types,
			'taxonomies' => $taxonomies,
		),
		'counts' => array(
			'post_types' => count( $post_types ),
			'taxonomies' => count( $taxonomies ),
		),
		'warnings' => array_unique( $warnings ),
	);
}

/**
 * Store import preview for the current user.
 *
 * @param array $preview Preview data.
 * @return void
 */
function bb_ct_set_import_preview( array $preview ): void {
	$user_id = get_current_user_id();
	set_transient( 'bb_ct_import_preview_' . $user_id, $preview, 10 * MINUTE_IN_SECONDS );
}

/**
 * Get import preview for the current user.
 *
 * @return array|null
 */
function bb_ct_get_import_preview(): ?array {
	$user_id = get_current_user_id();
	$data = get_transient( 'bb_ct_import_preview_' . $user_id );
	return is_array( $data ) ? $data : null;
}

/**
 * Clear import preview for the current user.
 *
 * @return void
 */
function bb_ct_clear_import_preview(): void {
	$user_id = get_current_user_id();
	delete_transient( 'bb_ct_import_preview_' . $user_id );
}
