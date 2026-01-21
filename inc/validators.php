<?php
/**
 * Validators and conflict checks.
 *
 * @package BB_Content_Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reserved slugs.
 *
 * @return array
 */
function bb_ct_reserved_slugs(): array {
	return array(
		'wp',
		'admin',
		'api',
		'tag',
		'category',
		'author',
		'feed',
		'attachment',
		'post',
		'page',
		'search',
		'wp-json',
		'json',
		'graphql',
	);
}

/**
 * Validate slug format.
 *
 * @param string $slug Slug.
 * @return bool
 */
function bb_ct_is_valid_slug( string $slug ): bool {
	return (bool) preg_match( '/^[a-z0-9\-]+$/', $slug );
}

/**
 * Detect slug conflicts.
 *
 * @param string $slug Slug.
 * @param array  $config Config.
 * @return array
 */
function bb_ct_check_slug_conflicts( string $slug, array $config ): array {
	$conflicts = array();

	if ( in_array( $slug, bb_ct_reserved_slugs(), true ) ) {
		$conflicts[] = 'Slug is reserved.';
	}

	foreach ( $config['post_types'] as $pt_slug => $pt ) {
		if ( $pt_slug === $slug ) {
			$conflicts[] = 'Slug matches existing post type.';
			break;
		}
		if ( ! empty( $pt['rewrite_base'] ) && $pt['rewrite_base'] === $slug ) {
			$conflicts[] = 'Rewrite base matches another post type.';
		}
		if ( ! empty( $pt['archive_slug'] ) && $pt['archive_slug'] === $slug ) {
			$conflicts[] = 'Archive slug matches another post type.';
		}
	}

	foreach ( $config['taxonomies'] as $tax_slug => $tax ) {
		if ( $tax_slug === $slug ) {
			$conflicts[] = 'Slug matches existing taxonomy.';
			break;
		}
	}

	$page = get_page_by_path( $slug, OBJECT, array( 'page', 'post' ) );
	if ( $page ) {
		$conflicts[] = 'Slug matches an existing post or page.';
	}

	$taxonomies = get_taxonomies( array(), 'names' );
	if ( in_array( $slug, $taxonomies, true ) ) {
		$conflicts[] = 'Slug matches a registered taxonomy base.';
	}

	return array_unique( $conflicts );
}
