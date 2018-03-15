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

		
		$sql = 'CREATE TABLE `protected_embeds` ( ' .
			'`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, ' .
			'`embed_id` varchar(64) NOT NULL, ' .
			'`src` varchar(255) NOT NULL, ' .
			'`embed_group_id` varchar(64) NOT NULL, ' .
			'`html` mediumtext, ' .
			'`time_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ' .
			'PRIMARY KEY (`id`), ' .
			'UNIQUE KEY `embed_id` (`embed_id`) ' .
			') ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$q = dbDelta( $sql );

		WP_CLI::line( $q[ 'protected_embeds' ] );

		$fd = fopen( $file, 'r' );
		$rows = fgetcsv( $fd );

		global $wpdb;
		$success = 0;
		$errors = 0;
		foreach ( $rows as $row ) {
			$insert = $wpdb->insert( 'protected_embeds', $row );
			if ( ! $insert ) {
				WP_CLI::warning( "Could not insert embed: `{$row['id']}`" );
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
}
