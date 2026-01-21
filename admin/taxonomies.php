<?php
/**
 * Taxonomies admin UI.
 *
 * @package BB_Content_Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_bb_ct_save_taxonomy', 'bb_ct_handle_save_taxonomy' );
add_action( 'admin_post_bb_ct_delete_taxonomy', 'bb_ct_handle_delete_taxonomy' );
add_action( 'admin_post_bb_ct_duplicate_taxonomy', 'bb_ct_handle_duplicate_taxonomy' );

/**
 * Render taxonomies page.
 *
 * @return void
 */
function bb_ct_render_taxonomies_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$config = bb_ct_get_config();
	$taxonomies = $config['taxonomies'];
	$post_types = array_keys( $config['post_types'] );
	$editing_slug = isset( $_GET['edit'] ) ? sanitize_key( wp_unslash( $_GET['edit'] ) ) : '';
	$editing = $editing_slug && isset( $taxonomies[ $editing_slug ] ) ? $taxonomies[ $editing_slug ] : null;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Content Types', 'bb-content-types' ); ?></h1>
		<p><?php esc_html_e( 'Taxonomies classify content across one or more content types (Department, Location, Topic). Create once, then attach where needed.', 'bb-content-types' ); ?></p>
		<h2><?php esc_html_e( 'Taxonomies', 'bb-content-types' ); ?></h2>

		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'Hierarchical', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'REST', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'Attached to', 'bb-content-types' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'bb-content-types' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $taxonomies ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'No taxonomies yet. Create a taxonomy and attach it to one or more content types.', 'bb-content-types' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $taxonomies as $slug => $tax ) : ?>
					<tr>
						<td><?php echo esc_html( $tax['plural'] ); ?></td>
						<td><?php echo esc_html( $slug ); ?></td>
						<td><?php echo ! empty( $tax['hierarchical'] ) ? esc_html__( 'Yes', 'bb-content-types' ) : esc_html__( 'No', 'bb-content-types' ); ?></td>
						<td><?php echo ! empty( $tax['show_in_rest'] ) ? esc_html__( 'Yes', 'bb-content-types' ) : esc_html__( 'No', 'bb-content-types' ); ?></td>
						<td><?php echo esc_html( implode( ', ', $tax['post_types'] ?? array() ) ); ?></td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=bb-content-types-taxonomies&edit=' . $slug ) ); ?>"><?php esc_html_e( 'Edit', 'bb-content-types' ); ?></a> |
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bb_ct_duplicate_taxonomy&slug=' . $slug ), 'bb_ct_duplicate_taxonomy' ) ); ?>"><?php esc_html_e( 'Duplicate', 'bb-content-types' ); ?></a> |
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bb_ct_delete_taxonomy&slug=' . $slug ), 'bb_ct_delete_taxonomy' ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete this taxonomy?', 'bb-content-types' ); ?>');"><?php esc_html_e( 'Delete', 'bb-content-types' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<hr />

		<h2><?php echo $editing ? esc_html__( 'Edit Taxonomy', 'bb-content-types' ) : esc_html__( 'Add New Taxonomy', 'bb-content-types' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'bb_ct_save_taxonomy' ); ?>
			<input type="hidden" name="action" value="bb_ct_save_taxonomy" />
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
						<p class="description"><?php esc_html_e( 'Lowercase, letters/numbers/hyphens only. Used in URLs and as the internal taxonomy key.', 'bb-content-types' ); ?></p>
						<?php if ( ! empty( $editing['conflicts'] ) ) : ?>
							<p class="description" style="color:#b32d2e;"><?php echo esc_html( implode( ' ', $editing['conflicts'] ) ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Hierarchical', 'bb-content-types' ); ?></th>
					<td><label><input type="checkbox" name="hierarchical" value="1" <?php checked( ! empty( $editing['hierarchical'] ) ); ?>> <?php esc_html_e( 'Category-style', 'bb-content-types' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show in REST', 'bb-content-types' ); ?></th>
					<td>
						<label><input type="checkbox" name="show_in_rest" value="1" <?php checked( ! empty( $editing['show_in_rest'] ) ); ?>> <?php esc_html_e( 'Expose in REST', 'bb-content-types' ); ?></label>
						<p class="description"><?php esc_html_e( 'Required for block editor and headless use cases.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show in admin columns', 'bb-content-types' ); ?></th>
					<td><label><input type="checkbox" name="show_admin_column" value="1" <?php checked( ! empty( $editing['show_admin_column'] ) ); ?>> <?php esc_html_e( 'Show in list table', 'bb-content-types' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Attach to Post Types', 'bb-content-types' ); ?></th>
					<td>
						<?php foreach ( $post_types as $pt_slug ) : ?>
							<label style="margin-right:12px;"><input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $pt_slug ); ?>" <?php checked( in_array( $pt_slug, $editing['post_types'] ?? array(), true ) ); ?>> <?php echo esc_html( $pt_slug ); ?></label>
						<?php endforeach; ?>
						<p class="description"><?php esc_html_e( 'A taxonomy must be attached to at least one content type to be used.', 'bb-content-types' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( $editing ? __( 'Update Taxonomy', 'bb-content-types' ) : __( 'Add Taxonomy', 'bb-content-types' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Handle duplicate taxonomy.
 *
 * @return void
 */
function bb_ct_handle_duplicate_taxonomy(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_duplicate_taxonomy' );

	$slug = isset( $_GET['slug'] ) ? sanitize_key( wp_unslash( $_GET['slug'] ) ) : '';
	$config = bb_ct_get_config();
	if ( ! isset( $config['taxonomies'][ $slug ] ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types-taxonomies&bb_ct_message=Not%20found' ) );
		exit;
	}

	$new_slug = $slug . '-copy';
	$config['taxonomies'][ $new_slug ] = $config['taxonomies'][ $slug ];
	$config['taxonomies'][ $new_slug ]['plural'] = $config['taxonomies'][ $slug ]['plural'] . ' Copy';
	$config['taxonomies'][ $new_slug ]['singular'] = $config['taxonomies'][ $slug ]['singular'] . ' Copy';
	bb_ct_save_config( $config );

	wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types-taxonomies&bb_ct_message=Duplicated' ) );
	exit;
}

/**
 * Handle save taxonomy.
 *
 * @return void
 */
function bb_ct_handle_save_taxonomy(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_save_taxonomy' );

	$config = bb_ct_get_config();
	$slug   = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
	if ( ! bb_ct_is_valid_slug( $slug ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types-taxonomies&bb_ct_message=Invalid%20slug' ) );
		exit;
	}

	$conflicts = bb_ct_check_slug_conflicts( $slug, $config );

	$data = array(
		'plural'            => sanitize_text_field( wp_unslash( $_POST['plural'] ?? '' ) ),
		'singular'          => sanitize_text_field( wp_unslash( $_POST['singular'] ?? '' ) ),
		'hierarchical'      => ! empty( $_POST['hierarchical'] ),
		'show_in_rest'      => ! empty( $_POST['show_in_rest'] ),
		'show_admin_column' => ! empty( $_POST['show_admin_column'] ),
		'post_types'        => array_map( 'sanitize_key', wp_unslash( $_POST['post_types'] ?? array() ) ),
		'conflicts'         => $conflicts,
	);

	$original_slug = isset( $_POST['original_slug'] ) ? sanitize_key( wp_unslash( $_POST['original_slug'] ) ) : '';
	if ( $original_slug && $original_slug !== $slug ) {
		unset( $config['taxonomies'][ $original_slug ] );
	}

	$config['taxonomies'][ $slug ] = $data;
	bb_ct_save_config( $config );

	wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types-taxonomies&bb_ct_message=Saved' ) );
	exit;
}

/**
 * Handle delete taxonomy.
 *
 * @return void
 */
function bb_ct_handle_delete_taxonomy(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	check_admin_referer( 'bb_ct_delete_taxonomy' );

	$slug = isset( $_GET['slug'] ) ? sanitize_key( wp_unslash( $_GET['slug'] ) ) : '';
	$config = bb_ct_get_config();
	unset( $config['taxonomies'][ $slug ] );
	bb_ct_save_config( $config );

	wp_safe_redirect( admin_url( 'admin.php?page=bb-content-types-taxonomies&bb_ct_message=Deleted' ) );
	exit;
}
