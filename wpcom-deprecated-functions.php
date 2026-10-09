<?php
/**
 * Deprecated WordPress.com functions.
 *
 * @package VIP_Go_WPCOM_Compat
 */

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
 *
 * @param string $slug Library name, as used in client-mu-plugins/lib/<slug>/<slug>.php.
 */
function require_lib( $slug ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	
	// Attempt to offer minimal back-compat.
	// If the lib happens to exist in client-mu-plugins/lib, load it.
	$lib = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/lib/' . $slug . '/' . $slug . '.php';
	if ( file_exists( $lib ) ) {
		require_once $lib;
	}
}

/**
 * Former WordPress.com VIP helper for Google stats.
 *
 * @deprecated Not applicable on VIP Go
 *
 * @param mixed $deprecated Unused.
 */
function vip_goog_stats( $deprecated = null ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Remove MP6 Styles
 *
 * At one point, this function was used to disable MP6 styles
 * on WP.com, but this is no longer applicable.
 * https://wordpress.org/plugins/mp6/
 *
 * @deprecated Not applicable on VIP Go
 */
function wpcom_vip_remove_mp6_styles() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Former WordPress.com VIP helper for bbPress 2 staff styles.
 *
 * @deprecated Not applicable on VIP Go
 */
function wpcom_vip_remove_bbpress2_staff_css() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Former WordPress.com VIP helper for capabilities in oEmbeds.
 *
 * @deprecated Not part of the VIP Go platform
 *
 * @param mixed $location Unused.
 */
function wpcom_vip_enabled_cap_in_oembed( $location = false ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Whether the code is running on WordPress.com VIP.
 *
 * @deprecated Not part of the VIP Go platform
 *
 * @return bool True if WPCOM_IS_VIP_ENV is defined as true.
 */
function is_wpcom_vip() {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	return defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV;
}

/**
 * Former WordPress.com VIP helper for protected embeds.
 *
 * @deprecated Not part of the VIP Go platform
 *
 * @param string $content Content.
 * @return string The content, unchanged.
 */
function wpcom_vip_protected_embed_to_original( $content ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $content;
}
