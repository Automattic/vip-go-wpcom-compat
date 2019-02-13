<?php
// Deprecated WordPress.com functions.

/**
 * Loads the built-in WP REST API endpoints in WordPress.com VIP context.
 *
 * @deprecated Not applicable since VIP 2.0.0
 */
function wpcom_vip_load_wp_rest_api() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * By default HTTP is forced to be the cannonical version of URLs on WordPress.com.
 *
 * @deprecated Not applicable on VIP Go
 */
function wpcom_vip_enable_https_canonical() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Requires WordPress.com internal libraries
 *
 * This internal function is used in some WordPress.com themes. These shared libraries
 * are no longer supported on VIP Go, so they will need to be copied directly into
 * VIP Go client repositories as needed.
 *
 * @deprecated Not supported on VIP Go
 */
function require_lib( $slug ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	
	// Attempt to offer minimal back-compat.
	// If the lib happens to exist in client-mu-plugins/lib, load it.
	$lib = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/lib/' . $slug . '/' . $slug . '.php';
	if ( file_exists( $lib ) ) {
		require_once( $lib );
	}
}

/*
 * @deprecated Not applicable on VIP Go
 */
function vip_goog_stats( $deprecated = null ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}
