<?php

class WPCOM_Compat_Command extends WPCOM_VIP_CLI_Command {

	/**
	 * Import protected embeds from WordPress.com
	 *
	 * # OPTIONS
	 *
	 * <file>
	 * : The CSV file to import
	 *
	 * @subcommand import-protected-embeds
	 */
	function import_protected_embeds( $args ) {
		list( $file ) = $args;

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( 'Specified file does not exist' );
		}

		$fd = fopen( $file, 'r' );
		if ( ! $fd ) {
			WP_CLI::error( sprintf( 'Could not open file: %s', $file ) );
		}

		$header = fgetcsv( $fd );
		if ( ! is_array( $header ) || count( $header ) !== 6 ||
			! in_array( 'id', $header, true ) ||
			! in_array( 'embed_id', $header, true ) ||
			! in_array( 'src', $header, true ) ||
			! in_array( 'embed_group_id', $header, true ) ||
			! in_array( 'html', $header, true ) ||
			! in_array( 'time_added', $header, true ) ) {
			WP_CLI::error( 'Invalid CSV, missing required fields' );
		}

		$sql = 'CREATE TABLE `protected_embeds` ( ' .
			'`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, ' .
			'`embed_id` varchar(64) NOT NULL, ' .
			'`src` varchar(255) NOT NULL, ' .
			'`embed_group_id` varchar(64) NOT NULL, ' .
			'`html` mediumtext, ' .
			'`time_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ' .
			'PRIMARY KEY (`id`), ' .
			'UNIQUE KEY `embed_id` (`embed_id`) ' .
			') ENGINE=InnoDB AUTO_INCREMENT=0';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$q = dbDelta( $sql );
		WP_CLI::line( $q['protected_embeds'] );

		global $wpdb;
		$success = 0;
		$errors  = 0;
		while ( $row = fgetcsv( $fd ) ) {
			$data = array_combine( $header, $row );
			if ( empty( $data ) || ! $data['id'] ) {
				continue;
			}

			$insert = $wpdb->insert( 'protected_embeds', $data );
			if ( ! $insert ) {
				WP_CLI::warning( "Could not insert embed: `{$data['id']}`" );
				WP_CLI::warning( $wpdb->last_error );
				$errors++;
			} else {
				$success++;
			}
		}

		if ( $errors < 1 ) {
			WP_CLI::success( 'Inserted all embeds without errors' );
		} else {
			WP_CLI::line( sprintf( 'Successfully inserted %d embeds', $success ) );
			WP_CLI::line( sprintf( 'Failed to insert %d embeds', $errors ) );
		}
	}

	/**
	 * Parses existent protected embeds from WordPress.com
	 *
	 * This will replace all the protected-iframe shortcodes with the correct rendered embed
	 *
	 * # OPTIONS
	 *
	 * <file>
	 *  : The CSV file with protected embeds
	 *
	 * @subcommand parse-protected-embeds
	 */
	function parse_protected_embeds( $args ) {
		$file = $args[0];

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( 'Specified file does not exist' );
		}

		$fd = fopen( $file, 'r' );
		if ( ! $fd ) {
			WP_CLI::error( sprintf( 'Could not open file: %s', $file ) );
		}

		$header = fgetcsv( $fd );
		if ( ! is_array( $header ) || count( $header ) !== 6 ||
			 ! in_array( 'id', $header, true ) ||
			 ! in_array( 'embed_id', $header, true ) ||
			 ! in_array( 'src', $header, true ) ||
			 ! in_array( 'embed_group_id', $header, true ) ||
			 ! in_array( 'html', $header, true ) ||
			 ! in_array( 'time_added', $header, true ) ) {
			WP_CLI::error( 'Invalid CSV, missing required fields' );
		}

		$protected_embeds = array();

		// Main CSV loop
		while ( $row = fgetcsv( $fd ) ) {
			$data = array_combine( $header, $row );
			if ( empty( $data ) || ! $data['id'] ) {
				continue;
			}

			$protected_embeds[ $data['embed_id'] ] = $data;
		}

		// Remove all shortcodes and add the protected-iframes handler
		global $shortcode_tags;
		$shortcode_tags = array();

		// Add custom protected-iframe handler
		add_shortcode( 'protected-iframe', function ( $attrs ) use ( $protected_embeds ) {
			return $protected_embeds[ $attrs['id'] ]['html'];
		} );

		// Query for posts with the protected-iframe shortcode
		WP_CLI::line( 'Looking for posts with protected-iframe shortcode.' );

		global $wpdb;
		$query = $wpdb->get_results( "SELECT ID, post_content FROM $wpdb->posts WHERE `post_content` LIKE '%protected-iframe%'" );

		$affected = 0;

		WP_CLI::line( sprintf( 'Found %d posts.', count( $query ) ) );
		sleep( 3 );

		foreach ( $query as $post ) {
			// Post does not have a protected-iframe, skip it
			if ( strpos( $post->post_content, 'protected-iframe' ) === false ) {
				continue;
			}

			$parsed_content = do_shortcode( $post->post_content );

			WP_CLI::line( " * Found {$post->ID}. Updating post content..." );

			// Update the post using $wpdb, to bypass any filters/options being called on `wp_insert_post`.
			$update = $wpdb->update( $wpdb->posts, array( 'post_content' => stripslashes( $parsed_content ) ), array( 'ID' => $post->ID ) );

			if ( ! $update ) {
				WP_CLI::error( sprintf( 'Error updating %d. Aborting. Updated %d posts so far.', $post->ID, $affected ) );
				return;
			}

			$affected++;

			if ( 0 === $affected % 100 ) {
				WP_CLI::line( '100 posts updated. Waiting 1 second...' );
				$this->stop_the_insanity();
				sleep( 1 );
			}
		}

		WP_CLI::success( sprintf( 'Done! %d posts updated.', $affected ) );
	}
}

WP_CLI::add_command( 'wpcom-compat', new WPCOM_Compat_Command );
