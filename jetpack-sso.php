<?php

/**
 * Jetpack SSO: Match WP.com accounts by email
 *
 * This ensures user accounts that have been imported from WordPress.com
 * are still associated with the same WP.com account for the purpose
 * of Jetpack SSO.
 */
add_filter( 'jetpack_sso_match_by_email', '__return_true', 9999 );
