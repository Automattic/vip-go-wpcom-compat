<?php
/**
 * Stand-ins for the VIP Platform code this plugin relies on, for wp-env and the tests.
 *
 * On VIP, vip-go-mu-plugins provides these before client mu-plugins load. wp-env maps
 * this file in as an mu-plugin so the plugin can load there too.
 *
 * @package Automattic\VIPGoWPCOMCompat\Tests
 */

if ( defined( 'WP_CLI' ) && WP_CLI && ! class_exists( 'WPCOM_VIP_CLI_Command' ) ) {
	/**
	 * Base class for VIP's WP-CLI commands.
	 */
	class WPCOM_VIP_CLI_Command extends WP_CLI_Command {} // phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
}

if ( ! function_exists( 'wpcom_vip_load_plugin' ) ) {
	/**
	 * Record the arguments instead of loading anything.
	 *
	 * @param string|false $plugin Plugin to load.
	 * @param string|false $folder Folder to load it from.
	 * @return bool Always true.
	 */
	function wpcom_vip_load_plugin( $plugin = false, $folder = false ) {
		$GLOBALS['wpcom_vip_load_plugin_args'] = array( $plugin, $folder );
		return true;
	}
}
