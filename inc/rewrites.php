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
add_filter( 'post_type_archive_link', 'bb_ct_apply_parent_archive_mapping', 10, 2 );

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
		if ( empty( $pt['enabled'] ) ) {
			continue;
		}
		if ( empty( $pt['parent_page_id'] ) ) {
			continue;
		}
		$parent_id = (int) $pt['parent_page_id'];
		$parent_path = trim( get_page_uri( $parent_id ), '/' );
		if ( '' === $parent_path ) {
			continue;
		}
		$rewrite_base = isset( $pt['rewrite_base'] ) && $pt['rewrite_base'] ? $pt['rewrite_base'] : $slug;
		$rewrite_base = trim( (string) $rewrite_base, '/' );
		if ( '' === $rewrite_base ) {
			continue;
		}
		$archive_slug = isset( $pt['archive_slug'] ) && $pt['archive_slug'] ? $pt['archive_slug'] : $rewrite_base;
		$archive_slug = trim( (string) $archive_slug, '/' );
		add_rewrite_rule(
			'^' . $parent_path . '/' . $rewrite_base . '/([^/]+)/?$',
			'index.php?post_type=' . $slug . '&name=$matches[1]',
			'top'
		);
		if ( ! empty( $pt['has_archive'] ) ) {
			add_rewrite_rule(
				'^' . $parent_path . '/' . $archive_slug . '/?$',
				'index.php?post_type=' . $slug,
				'top'
			);
		}
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
	if ( 'publish' !== $post->post_status ) {
		return $post_link;
	}
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

	$pt = $config['post_types'][ $post->post_type ];
	$rewrite_base = isset( $pt['rewrite_base'] ) && $pt['rewrite_base'] ? $pt['rewrite_base'] : $post->post_type;
	$rewrite_base = trim( (string) $rewrite_base, '/' );
	if ( '' === $rewrite_base || '' === $post->post_name ) {
		return $post_link;
	}

	return home_url( user_trailingslashit( $parent_path . '/' . $rewrite_base . '/' . $post->post_name ) );
}

/**
 * Apply parent page mapping to CPT archive links.
 *
 * @param string $link Archive link.
 * @param string $post_type Post type.
 * @return string
 */
function bb_ct_apply_parent_archive_mapping( string $link, string $post_type ): string {
	$config = bb_ct_get_config();
	if ( empty( $config['post_types'][ $post_type ]['parent_page_id'] ) ) {
		return $link;
	}
	$pt = $config['post_types'][ $post_type ];
	if ( empty( $pt['has_archive'] ) ) {
		return $link;
	}
	$parent_id = (int) $pt['parent_page_id'];
	$parent_path = trim( get_page_uri( $parent_id ), '/' );
	if ( '' === $parent_path ) {
		return $link;
	}
	$archive_slug = isset( $pt['archive_slug'] ) && $pt['archive_slug'] ? $pt['archive_slug'] : ( $pt['rewrite_base'] ?? $post_type );
	$archive_slug = trim( (string) $archive_slug, '/' );
	if ( '' === $archive_slug ) {
		return $link;
	}
	return home_url( user_trailingslashit( $parent_path . '/' . $archive_slug ) );
}
