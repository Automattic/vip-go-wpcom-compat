<?php
/**
 * WordPress.com Compatibility
 *
 * @package           VIP_Go_WPCOM_Compat
 * @author            Automattic, WordPress VIP
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress.com Compatibility
 * Plugin URI:        https://github.com/Automattic/vip-go-wpcom-compat
 * Description:       Compatibility shims for sites that moved from WordPress.com VIP to the WordPress VIP Platform.
 * Version:           1.0.1
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Automattic, WordPress VIP
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/class-wpcom-compat-command.php';
}

require_once __DIR__ . '/wpcom-deprecated-functions.php';
require_once __DIR__ . '/wpcom-shortcodes.php';
require_once __DIR__ . '/wpcom-plugins.php';
require_once __DIR__ . '/jetpack-sso.php';
require_once __DIR__ . '/wpcom-hooks.php';
require_once __DIR__ . '/wpcom-sitemap.php';
