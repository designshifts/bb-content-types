<?php
/**
 * Uninstall cleanup.
 *
 * @package BB_Content_Types
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bb_content_types_config' );
delete_option( 'bb_content_types_needs_flush' );

global $wpdb;
if ( $wpdb instanceof wpdb ) {
	$like = $wpdb->esc_like( '_transient_bb_ct_import_preview_' ) . '%';
	$timeout_like = $wpdb->esc_like( '_transient_timeout_bb_ct_import_preview_' ) . '%';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $timeout_like ) );
}
