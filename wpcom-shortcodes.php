<?php

add_shortcode( 'protected-iframe', 'wpcom_compat_protected_iframe_shortcode' );

function wpcom_compat_protected_iframe_shortcode( $attrs ) {
	$attrs = wp_parse_args( $attrs, array(
		'id' => null,
	) );

	$id    = $attrs['id'];
	$embed = wp_cache_get( $id, 'simple-protected-embeds' );

	if ( false === $embed ) {
		global $wpdb;

		$embed = $wpdb->get_row(
			$wpdb->prepare( "SELECT html FROM `protected_embeds` WHERE `embed_id` = %s", $id )
		);

		if ( ! $embed ) {
			return '<!-- Embed not found -->';
		}

		$embed = $embed->html;

		wp_cache_set( $id, $embed, 'simple-protected-embeds' );
	}

	return $embed;
}
