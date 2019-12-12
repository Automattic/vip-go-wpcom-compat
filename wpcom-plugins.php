<?php

/**
 * Loads a plugin on VIP Go, with compatability for the way things were done on WPcom.
 * Compatability for both WPcom's deprecated plugin versioning and the deprecated folder locations (theme & plugin).
 *
 * @param string $plugin Optional. Folder name of the plugin, or the folder and plugin file name (such as wp-api/plugin.php), relative to WP_PLUGIN_DIR.
 * @param string $folder Subdirectory of WP_PLUGIN_DIR to load plugin from.
 * @param string $version Optional. Specify which version of the plugin to load. Version should be in the format 1.0.0.
 *
 * @return bool True if the include was successful.
 */
function wpcom_vip_legacy_load_plugin( $plugin = false, $folder = false, $version = false ) {
	if ( is_string( $version ) && false !== $plugin ) {
		$plugin = "$plugin-$version/$plugin.php";
	}

	if ( in_array( $folder, [ 'theme', 'plugins' ], true ) ) {
		$folder = false;
	}

	return wpcom_vip_load_plugin( $plugin, $folder );
}

// Enables the Writing Helper plugin that is a part of WordPress.com but not Jetpack.
if ( true === apply_filters( 'wpcom_compat_enable_writing_helper', true ) && ! class_exists( 'Writing_Helper' ) ) {
	require __DIR__ . '/plugins/writing-helper/writing-helper.php';
}

require_once __DIR__ . '/plugins/mrss.php';
