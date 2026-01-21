<?php
/**
 * Config helpers.
 *
 * @package BB_Content_Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get config with defaults.
 *
 * @return array
 */
function bb_ct_get_config(): array {
	$config = get_option( BB_CT_CONFIG_OPTION, array() );
	if ( ! is_array( $config ) ) {
		$config = array();
	}

	return array_merge(
		array(
			'version'    => 1,
			'post_types' => array(),
			'taxonomies' => array(),
		),
		$config
	);
}

/**
 * Save config.
 *
 * @param array $config Config.
 * @return void
 */
function bb_ct_save_config( array $config ): void {
	update_option( BB_CT_CONFIG_OPTION, $config );
	bb_ct_mark_needs_flush();
}

/**
 * Mark rewrite flush needed.
 *
 * @return void
 */
function bb_ct_mark_needs_flush(): void {
	update_option( BB_CT_NEEDS_FLUSH_OPTION, 1 );
}

/**
 * Clear rewrite flush flag.
 *
 * @return void
 */
function bb_ct_clear_needs_flush(): void {
	delete_option( BB_CT_NEEDS_FLUSH_OPTION );
}
