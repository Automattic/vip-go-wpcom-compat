<?php
/**
 * Plugin Name: WordPress.com Compatibility
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/class-wpcom-compat-command.php';
}

// Deprecated WordPress.com functions.

/**
 * Loads the built-in WP REST API endpoints in WordPress.com VIP context.
 *
 * @deprecated Not applicable since VIP 2.0.0
 */
function wpcom_vip_load_wp_rest_api() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}
