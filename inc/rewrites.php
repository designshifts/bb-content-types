<?php
/**
 * Rewrite handling and parent mapping.
 *
 * @package BB_Content_Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'bb_ct_maybe_flush_rewrites' );
add_action( 'init', 'bb_ct_register_parent_rewrites', 20 );
add_filter( 'post_type_link', 'bb_ct_apply_parent_mapping', 10, 2 );

/**
 * Flush rewrite rules if needed (admin-only).
 *
 * @return void
 */
function bb_ct_maybe_flush_rewrites(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! get_option( BB_CT_NEEDS_FLUSH_OPTION ) ) {
		return;
	}

	flush_rewrite_rules();
	bb_ct_clear_needs_flush();
}

/**
 * Register rewrite rules for parent page mappings.
 *
 * @return void
 */
function bb_ct_register_parent_rewrites(): void {
	$config = bb_ct_get_config();
	foreach ( $config['post_types'] as $slug => $pt ) {
		if ( empty( $pt['parent_page_id'] ) ) {
			continue;
		}
		$parent_id = (int) $pt['parent_page_id'];
		$parent_path = trim( get_page_uri( $parent_id ), '/' );
		if ( '' === $parent_path ) {
			continue;
		}
		add_rewrite_rule(
			'^' . $parent_path . '/([^/]+)/?$',
			'index.php?post_type=' . $slug . '&name=$matches[1]',
			'top'
		);
	}
}

/**
 * Apply parent page mapping to CPT permalinks.
 *
 * @param string  $post_link Link.
 * @param WP_Post $post Post.
 * @return string
 */
function bb_ct_apply_parent_mapping( string $post_link, WP_Post $post ): string {
	$config = bb_ct_get_config();
	if ( empty( $config['post_types'][ $post->post_type ]['parent_page_id'] ) ) {
		return $post_link;
	}

	$parent_id = (int) $config['post_types'][ $post->post_type ]['parent_page_id'];
	$parent    = get_post( $parent_id );
	if ( ! $parent ) {
		return $post_link;
	}

	$parent_path = trim( get_page_uri( $parent_id ), '/' );
	if ( '' === $parent_path ) {
		return $post_link;
	}

	return home_url( '/' . $parent_path . '/' . $post->post_name . '/' );
}
