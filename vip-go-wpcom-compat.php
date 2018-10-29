<?php
/**
 * Plugin Name: WordPress.com Compatibility
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/class-wpcom-compat-command.php';
}

// Dynamic rewrites for intermediate image sizes https://github.com/Automattic/vip-go-mu-plugins/pull/515
define( 'WPCOM_VIP_USE_JETPACK_PHOTON', true );

require_once __DIR__ . '/wpcom-deprecated-functions.php';
require_once __DIR__ . '/wpcom-shortcodes.php';
require_once __DIR__ . '/wpcom-hooks.php';
