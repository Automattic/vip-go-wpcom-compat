<?php

/**
 * Provides simple backwards compatability with WordPress.com Protected Embeds.
 * This does NOT provide the "protection" of the protected embeds, just renders them.
 *
 * If a site wants to use a different protected embeds plugin, they can by calling
 * `remove_shortcode( 'protected-iframe' )` before loading the other plugin.
 */
function wpcom_compat_protected_iframe_shortcode( $attrs ) {
	$attrs = wp_parse_args(
		$attrs, array(
			'id' => null,
		)
	);

	$id    = $attrs['id'];
	$embed = wp_cache_get( $id, 'simple-protected-embeds' );

	if ( false === $embed ) {
		global $wpdb;

		$embed = $wpdb->get_row(
			$wpdb->prepare( 'SELECT html FROM `protected_embeds` WHERE `embed_id` = %s', $id )
		);

		if ( ! $embed ) {
			return '<!-- Embed not found -->';
		}

		$embed = $embed->html;

		wp_cache_set( $id, $embed, 'simple-protected-embeds' );
	}

	return $embed;
}
add_shortcode( 'protected-iframe', 'wpcom_compat_protected_iframe_shortcode' );
