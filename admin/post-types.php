<?php
/**
 * Post Types admin UI.
 *
 * @package BB_Content_Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_bb_ct_save_post_type', 'bb_ct_handle_save_post_type' );
add_action( 'admin_post_bb_ct_delete_post_type', 'bb_ct_handle_delete_post_type' );
add_action( 'admin_post_bb_ct_duplicate_post_type', 'bb_ct_handle_duplicate_post_type' );
add_action( 'admin_post_bb_ct_toggle_post_type', 'bb_ct_handle_toggle_post_type' );

/**
 * Render post types page.
 *
 * @return void
 */
function bb_ct_render_post_types_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$config = bb_ct_get_config();
	$post_types = $config['post_types'];
	$taxonomies = $config['taxonomies'];
	$editing_slug = isset( $_GET['edit'] ) ? sanitize_key( wp_unslash( $_GET['edit'] ) ) : '';
	$editing = $editing_slug && isset( $post_types[ $editing_slug ] ) ? $post_types[ $editing_slug ] : null;
	?>
	<div class="wrap">
		<?php bb_ct_render_page_header( __( 'Post Types', 'bb-content-types' ), 'database', __( 'Content Types', 'bb-content-types' ) ); ?>
		<p><?php esc_html_e( 'Define custom post types, taxonomies, and URL behavior with predictable settings that can be safely handed off.', 'bb-content-types' ); ?></p>
		<p>
			<?php
			printf(
				'<a class="button" href="%s">%s</a>',
				esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bb_ct_flush_rewrites' ), 'bb_ct_flush_rewrites' ) ),
				esc_html__( 'Flush rewrites', 'bb-content-types' )
			);
			?>
		</p>
		<h2><?php esc_html_e( 'Post Types', 'bb-content-types' ); ?></h2>

		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'Status', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'Archive', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'REST', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'URL Base', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'Attached Taxonomies', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'Mapped Under', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'bb-content-types' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $post_types ) ) : ?>
					<tr><td colspan="9"><?php esc_html_e( 'No content types yet. Add a custom post type to model your site content (Careers, Case Studies, Docs, Events). You can adjust URLs and attach taxonomies later.', 'bb-content-types' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $post_types as $slug => $pt ) : ?>
					<tr>
						<td><?php echo esc_html( $pt['plural'] ); ?></td>
						<td><?php echo esc_html( $slug ); ?></td>
						<td><?php echo ! empty( $pt['enabled'] ) ? esc_html__( 'Enabled', 'bb-content-types' ) : esc_html__( 'Disabled', 'bb-content-types' ); ?></td>
						<td><?php echo ! empty( $pt['has_archive'] ) ? esc_html__( 'Yes', 'bb-content-types' ) : esc_html__( 'No', 'bb-content-types' ); ?></td>
						<td><?php echo ! empty( $pt['show_in_rest'] ) ? esc_html__( 'Yes', 'bb-content-types' ) : esc_html__( 'No', 'bb-content-types' ); ?></td>
						<td><?php echo esc_html( $pt['rewrite_base'] ?? $slug ); ?></td>
						<td>
							<?php
							$attached = array();
							foreach ( $taxonomies as $tax_slug => $tax ) {
								if ( in_array( $slug, $tax['post_types'] ?? array(), true ) ) {
									$attached[] = $tax_slug;
								}
							}
							echo esc_html( implode( ', ', $attached ) );
							?>
						</td>
						<td>
							<?php
							if ( ! empty( $pt['parent_page_id'] ) ) {
								$page = get_post( (int) $pt['parent_page_id'] );
								echo $page ? esc_html( $page->post_title ) : '—';
							} else {
								echo '—';
							}
							?>
						</td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=bb-content-types&edit=' . $slug ) ); ?>"><?php esc_html_e( 'Edit', 'bb-content-types' ); ?></a> |
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bb_ct_duplicate_post_type&slug=' . $slug ), 'bb_ct_duplicate_post_type' ) ); ?>"><?php esc_html_e( 'Duplicate', 'bb-content-types' ); ?></a> |
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bb_ct_toggle_post_type&slug=' . $slug ), 'bb_ct_toggle_post_type' ) ); ?>" <?php echo ! empty( $pt['enabled'] ) ? 'onclick="return confirm(\'Disable this content type? Existing content will remain in the database but won’t be registered until re-enabled.\');"' : ''; ?>>
								<?php echo ! empty( $pt['enabled'] ) ? esc_html__( 'Disable', 'bb-content-types' ) : esc_html__( 'Enable', 'bb-content-types' ); ?>
							</a> |
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bb_ct_delete_post_type&slug=' . $slug ), 'bb_ct_delete_post_type' ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete this content type? Existing content will remain in the database but will no longer be registered.', 'bb-content-types' ); ?>');"><?php esc_html_e( 'Delete', 'bb-content-types' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<hr />

		<h2><?php echo $editing ? esc_html__( 'Edit Post Type', 'bb-content-types' ) : esc_html__( 'Add New Post Type', 'bb-content-types' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'bb_ct_save_post_type' ); ?>
			<input type="hidden" name="action" value="bb_ct_save_post_type" />
			<?php if ( $editing_slug ) : ?>
				<input type="hidden" name="original_slug" value="<?php echo esc_attr( $editing_slug ); ?>" />
			<?php endif; ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Plural Name', 'bb-content-types' ); ?></th>
					<td>
						<input type="text" name="plural" class="regular-text" value="<?php echo esc_attr( $editing['plural'] ?? '' ); ?>" required>
						<p class="description"><?php esc_html_e( 'Shown in the admin menu and list screens.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Singular Name', 'bb-content-types' ); ?></th>
					<td>
						<input type="text" name="singular" class="regular-text" value="<?php echo esc_attr( $editing['singular'] ?? '' ); ?>" required>
						<p class="description"><?php esc_html_e( 'Used for editor labels and buttons.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Slug', 'bb-content-types' ); ?></th>
					<td>
						<input type="text" name="slug" class="regular-text" value="<?php echo esc_attr( $editing_slug ); ?>" required>
						<p class="description"><?php esc_html_e( 'Lowercase, letters/numbers/hyphens only. Used in URLs and as the internal post type key.', 'bb-content-types' ); ?></p>
						<?php if ( ! empty( $editing['conflicts'] ) ) : ?>
							<p class="description" style="color:#b32d2e;"><?php echo esc_html( implode( ' ', $editing['conflicts'] ) ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Description', 'bb-content-types' ); ?></th>
					<td>
						<textarea name="description" class="large-text"><?php echo esc_textarea( $editing['description'] ?? '' ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Optional. Helps teams understand what this content type is for.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Icon', 'bb-content-types' ); ?></th>
					<td>
						<select name="icon">
							<?php
							$icons = array( 'dashicons-admin-post', 'dashicons-portfolio', 'dashicons-id', 'dashicons-media-document', 'dashicons-category' );
							foreach ( $icons as $icon ) {
								printf(
									'<option value="%1$s" %2$s>%1$s</option>',
									esc_attr( $icon ),
									selected( $editing['icon'] ?? 'dashicons-admin-post', $icon, false )
								);
							}
							?>
						</select>
						<p class="description"><?php esc_html_e( 'Dashicon used in the WordPress admin menu.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Supports', 'bb-content-types' ); ?></th>
					<td>
						<?php
						$supports = $editing['supports'] ?? array( 'title', 'editor' );
						$choices  = array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' );
						foreach ( $choices as $choice ) {
							printf(
								'<label style="margin-right:12px;"><input type="checkbox" name="supports[]" value="%1$s" %2$s> %1$s</label>',
								esc_attr( $choice ),
								checked( in_array( $choice, $supports, true ), true, false )
							);
						}
						?>
						<p class="description"><?php esc_html_e( 'Choose which editor features are enabled for this content type.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Visibility', 'bb-content-types' ); ?></th>
					<td>
						<label><input type="checkbox" name="public" value="1" <?php checked( ! empty( $editing['public'] ) ); ?>> <?php esc_html_e( 'Public', 'bb-content-types' ); ?></label>
						<p class="description"><?php esc_html_e( 'If disabled, content is admin-only and not publicly queryable.', 'bb-content-types' ); ?></p>
						<label><input type="checkbox" name="has_archive" value="1" <?php checked( ! empty( $editing['has_archive'] ) ); ?>> <?php esc_html_e( 'Has archive', 'bb-content-types' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enable an archive page for this content type.', 'bb-content-types' ); ?></p>
						<label><input type="checkbox" name="hierarchical" value="1" <?php checked( ! empty( $editing['hierarchical'] ) ); ?>> <?php esc_html_e( 'Hierarchical', 'bb-content-types' ); ?></label><br>
						<label><input type="checkbox" name="show_in_rest" value="1" <?php checked( ! empty( $editing['show_in_rest'] ) ); ?>> <?php esc_html_e( 'Show in REST', 'bb-content-types' ); ?></label>
						<p class="description"><?php esc_html_e( 'Required for block editor and headless use cases.', 'bb-content-types' ); ?></p>
						<label><input type="checkbox" name="show_in_menu" value="1" <?php checked( ! empty( $editing['show_in_menu'] ) ); ?>> <?php esc_html_e( 'Show in Admin Menu', 'bb-content-types' ); ?></label>
						<p class="description"><?php esc_html_e( 'If disabled, content is still accessible but not shown in the left menu.', 'bb-content-types' ); ?></p>
						<label><input type="checkbox" name="exclude_from_search" value="1" <?php checked( ! empty( $editing['exclude_from_search'] ) ); ?>> <?php esc_html_e( 'Exclude from search', 'bb-content-types' ); ?></label>
						<p class="description"><?php esc_html_e( 'Prevents content from appearing in site search results.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Rewrite Base', 'bb-content-types' ); ?></th>
					<td>
						<input type="text" name="rewrite_base" class="regular-text" value="<?php echo esc_attr( $editing['rewrite_base'] ?? '' ); ?>">
						<p class="description"><?php esc_html_e( 'The base path used for single URLs. Default is the slug.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'With front', 'bb-content-types' ); ?></th>
					<td>
						<label><input type="checkbox" name="with_front" value="1" <?php checked( ! empty( $editing['with_front'] ) ); ?>> <?php esc_html_e( 'Use front base', 'bb-content-types' ); ?></label>
						<p class="description"><?php esc_html_e( 'If enabled, WordPress will prefix URLs with your site’s permalink front base.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Archive slug', 'bb-content-types' ); ?></th>
					<td>
						<input type="text" name="archive_slug" class="regular-text" value="<?php echo esc_attr( $editing['archive_slug'] ?? '' ); ?>">
						<p class="description"><?php esc_html_e( 'Optional. Defaults to the rewrite base.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Parent Page Mapping', 'bb-content-types' ); ?></th>
					<td>
						<?php
						wp_dropdown_pages(
							array(
								'name'              => 'parent_page_id',
								'selected'          => $editing['parent_page_id'] ?? 0,
								'show_option_none'  => __( '— None —', 'bb-content-types' ),
							)
						);
						?>
						<p class="description"><?php esc_html_e( 'Prepends the selected page path to single URLs (example: /company/careers/job-title/).', 'bb-content-types' ); ?></p>
						<p class="description"><?php esc_html_e( 'Parent mapping affects single URLs only. If you change this later, consider adding redirects.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( $editing ? __( 'Update Post Type', 'bb-content-types' ) : __( 'Add Post Type', 'bb-content-types' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Handle save post type.
 *
 * @return void
 */
function bb_ct_handle_save_post_type(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_save_post_type' );

	$config = bb_ct_get_config();
	$slug   = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
	if ( ! bb_ct_is_valid_slug( $slug ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types&bb_ct_message=Invalid%20slug' ) );
		exit;
	}

	$conflicts = bb_ct_check_slug_conflicts( $slug, $config );
	$rewrite_base = sanitize_key( wp_unslash( $_POST['rewrite_base'] ?? $slug ) );
	$archive_slug = sanitize_key( wp_unslash( $_POST['archive_slug'] ?? '' ) );
	if ( $rewrite_base && $rewrite_base !== $slug ) {
		$conflicts = array_merge( $conflicts, bb_ct_check_slug_conflicts( $rewrite_base, $config ) );
	}
	if ( $archive_slug ) {
		$conflicts = array_merge( $conflicts, bb_ct_check_slug_conflicts( $archive_slug, $config ) );
	}
	$parent_page_id = absint( $_POST['parent_page_id'] ?? 0 );
	if ( $parent_page_id ) {
		$parent_path = trim( get_page_uri( $parent_page_id ), '/' );
		if ( $parent_path && $parent_path === $rewrite_base ) {
			$conflicts[] = 'Parent mapping matches rewrite base.';
		}
		if ( $parent_path && $archive_slug && $parent_path === $archive_slug ) {
			$conflicts[] = 'Parent mapping matches archive slug.';
		}
	}

	$data = array(
		'plural'              => sanitize_text_field( wp_unslash( $_POST['plural'] ?? '' ) ),
		'singular'            => sanitize_text_field( wp_unslash( $_POST['singular'] ?? '' ) ),
		'description'         => sanitize_text_field( wp_unslash( $_POST['description'] ?? '' ) ),
		'icon'                => sanitize_text_field( wp_unslash( $_POST['icon'] ?? 'dashicons-admin-post' ) ),
		'supports'            => array_map( 'sanitize_text_field', wp_unslash( $_POST['supports'] ?? array() ) ),
		'public'              => ! empty( $_POST['public'] ),
		'has_archive'         => ! empty( $_POST['has_archive'] ),
		'hierarchical'        => ! empty( $_POST['hierarchical'] ),
		'show_in_rest'        => ! empty( $_POST['show_in_rest'] ),
		'show_in_menu'        => ! empty( $_POST['show_in_menu'] ),
		'exclude_from_search' => ! empty( $_POST['exclude_from_search'] ),
		'rewrite_base'         => $rewrite_base,
		'with_front'           => ! empty( $_POST['with_front'] ),
		'archive_slug'         => $archive_slug,
		'parent_page_id'       => $parent_page_id,
		'enabled'              => true,
		'conflicts'            => $conflicts,
	);

	$original_slug = isset( $_POST['original_slug'] ) ? sanitize_key( wp_unslash( $_POST['original_slug'] ) ) : '';
	if ( $original_slug && $original_slug !== $slug ) {
		unset( $config['post_types'][ $original_slug ] );
	}

	$config['post_types'][ $slug ] = $data;
	bb_ct_save_config( $config );

	wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types&bb_ct_message=Saved' ) );
	exit;
}

/**
 * Handle delete post type.
 *
 * @return void
 */
function bb_ct_handle_delete_post_type(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_delete_post_type' );

	$slug = isset( $_GET['slug'] ) ? sanitize_key( wp_unslash( $_GET['slug'] ) ) : '';
	$config = bb_ct_get_config();
	unset( $config['post_types'][ $slug ] );
	bb_ct_save_config( $config );

	wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types&bb_ct_message=Deleted' ) );
	exit;
}

/**
 * Handle duplicate post type.
 *
 * @return void
 */
function bb_ct_handle_duplicate_post_type(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_duplicate_post_type' );

	$slug = isset( $_GET['slug'] ) ? sanitize_key( wp_unslash( $_GET['slug'] ) ) : '';
	$config = bb_ct_get_config();
	if ( ! isset( $config['post_types'][ $slug ] ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types&bb_ct_message=Not%20found' ) );
		exit;
	}

	$new_slug = $slug . '-copy';
	$config['post_types'][ $new_slug ] = $config['post_types'][ $slug ];
	$config['post_types'][ $new_slug ]['plural'] = $config['post_types'][ $slug ]['plural'] . ' Copy';
	$config['post_types'][ $new_slug ]['singular'] = $config['post_types'][ $slug ]['singular'] . ' Copy';
	$config['post_types'][ $new_slug ]['enabled'] = false;
	bb_ct_save_config( $config );

	wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types&bb_ct_message=Duplicated' ) );
	exit;
}

/**
 * Toggle post type enabled.
 *
 * @return void
 */
function bb_ct_handle_toggle_post_type(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_toggle_post_type' );

	$slug = isset( $_GET['slug'] ) ? sanitize_key( wp_unslash( $_GET['slug'] ) ) : '';
	$config = bb_ct_get_config();
	if ( isset( $config['post_types'][ $slug ] ) ) {
		$config['post_types'][ $slug ]['enabled'] = ! empty( $config['post_types'][ $slug ]['enabled'] ) ? false : true;
		bb_ct_save_config( $config );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types&bb_ct_message=Updated' ) );
	exit;
}
