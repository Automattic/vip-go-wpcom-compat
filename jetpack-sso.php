<?php
/**
 * Jetpack SSO: Match WP.com accounts by email
 *
 * This ensures user accounts that have been imported from WordPress.com
 * are still associated with the same WP.com account for the purpose
 * of Jetpack SSO.
 *
 * @package VIP_Go_WPCOM_Compat
 */

add_filter( 'jetpack_sso_match_by_email', '__return_true', 9999 );

add_filter( 'jetpack_active_modules', 'vip_wpcom_compat_enable_jetpack_sso', 9999 );

/**
 * Add SSO to the active Jetpack modules.
 *
 * @param array $modules Active Jetpack module slugs.
 * @return array Active Jetpack module slugs, including sso.
 */
function vip_wpcom_compat_enable_jetpack_sso( $modules ) {
	$modules[] = 'sso';
	return array_unique( $modules );
}
