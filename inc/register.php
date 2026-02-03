<?php
/**
 * Runtime registration pipeline.
 *
 * @package BB_Content_Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'bb_ct_register_from_config', 5 );

/**
 * Register taxonomies then post types from config.
 *
 * @return void
 */
function bb_ct_register_from_config(): void {
	$config = bb_ct_get_config();

	foreach ( $config['taxonomies'] as $slug => $tax ) {
		$args = array(
			'labels'            => array(
				'name'          => $tax['plural'],
				'singular_name' => $tax['singular'],
			),
			'hierarchical'      => ! empty( $tax['hierarchical'] ),
			'show_in_rest'      => ! empty( $tax['show_in_rest'] ),
			'show_admin_column' => ! empty( $tax['show_admin_column'] ),
		);
		$object_types = ! empty( $tax['post_types'] ) ? $tax['post_types'] : array();
		register_taxonomy( $slug, $object_types, $args );
	}

	foreach ( $config['post_types'] as $slug => $pt ) {
		if ( empty( $pt['enabled'] ) ) {
			continue;
		}
		$supports = ! empty( $pt['supports'] ) ? $pt['supports'] : array( 'title', 'editor' );
		$rewrite_base = $pt['rewrite_base'] ?? $slug;
		if ( '' === $rewrite_base ) {
			$rewrite_base = $slug;
		}
		$has_archive  = ! empty( $pt['has_archive'] );
		$archive_slug = ! empty( $pt['archive_slug'] ) ? $pt['archive_slug'] : $rewrite_base;

		$args = array(
			'labels'              => array(
				'name'          => $pt['plural'],
				'singular_name' => $pt['singular'],
			),
			'description'         => $pt['description'] ?? '',
			'public'              => ! empty( $pt['public'] ),
			'publicly_queryable'  => ! empty( $pt['public'] ),
			'has_archive'         => $has_archive ? $archive_slug : false,
			'hierarchical'        => ! empty( $pt['hierarchical'] ),
			'show_in_rest'        => ! empty( $pt['show_in_rest'] ),
			'show_in_menu'        => ! empty( $pt['show_in_menu'] ),
			'exclude_from_search' => ! empty( $pt['exclude_from_search'] ),
			'supports'            => $supports,
			'menu_icon'           => $pt['icon'] ?? 'dashicons-admin-post',
			'rewrite'             => array(
				'slug'       => $rewrite_base,
				'with_front' => ! empty( $pt['with_front'] ),
			),
		);

		register_post_type( $slug, $args );
	}
}
