<?php

/**
 * Filter the arguments that are passed into wpcom_vip_load_plugin().
 * Extends support for compability with WPcom's deprecated plugin versioning and deprecated folder locations.
 * 
 * @param $args [ 'plugin' => $plugin, 'folder' => $folder ]
 * @param $version Deprecated $load_release_candidate_not_used
 * @return $args [ 'plugin' => 'plugin-slug-1.2/plugin-slug.php', 'folder' => 'custom-folder' ]
 */
function wpcom_compat_load_plugin_args( $args, $version ) {
	if ( ! empty( $args['plugin'] ) && ! empty( $version ) && is_string( $args['plugin'] ) && is_string( $version ) ) {
		$args['plugin'] = "{$args['plugin']}-{$version}/{$args['plugin']}.php";
	}

	if ( ! empty( $args['folder'] ) && in_array( $args['folder'], [ 'theme', 'plugins' ], true ) ) {
		$args['folder'] = false;
	}

	return $args;
}
add_filter( 'wpcom_vip_load_plugin_args','wpcom_compat_load_plugin_args', 10, 2 );

// Enables the Writing Helper plugin that is a part of WordPress.com but not Jetpack.
if ( true === apply_filters( 'wpcom_compat_enable_writing_helper', true ) && ! class_exists( 'Writing_Helper' ) ) {
	require __DIR__ . '/plugins/writing-helper/writing-helper.php';
}
