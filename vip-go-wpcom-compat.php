<?php
/**
 * Plugin Name: WordPress.com Compatibility
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/class-wpcom-compat-command.php';
}

require_once __DIR__ . '/wpcom-deprecated-functions.php';
require_once __DIR__ . '/wpcom-shortcodes.php';

/**
 * Jetpack SSO: Match WP.com accounts by email
 *
 * This ensures user accounts that have been imported from WordPress.com
 * are still associated with the same WP.com account for the purpose
 * of Jetpack SSO.
 */
add_filter( 'jetpack_sso_match_by_email', '__return_true', 9999 );
