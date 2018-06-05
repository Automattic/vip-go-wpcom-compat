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
	 * Update user IDs after importing.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : The CSV file to import from.
	 *
	 * [--user_key=<userlogin>]
	 * : The `user_key` is the "key" used to uniquely identify a user, a property of the `WP_User` object.  Can be one of the following: ID, user_nicename, user_email, user_login. Defaults to user_login.
	 *
	 * [--dry-run=<true>]
	 * : Do a "dry run" and no data modification will be done.  Defaults to true.
	 *
	 * @subcommand update-user-ids
	*/
	public function update_user_ids( $args, $assoc_args ) {
		$filename = $args[0];
		$user_key = sanitize_text_field( $assoc_args['user_key'] ) ?? 'user_login';
		if ( false === in_array( $user_key, array( 'ID', 'user_nicename', 'user_email', 'user_login' ), true ) ) {
			$user_key = 'user_login';
		}
		$dry_run = Utils\get_flag_value( $assoc_args, 'dry-run', true );

		// Force a boolean, always default to true.
		$dry_run = filter_var( $dry_run, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? true;

		if ( ! $dry_run ) {
			WP_CLI::confirm( 'This can really mess up a site if used wrong. Are you sure?' );
			WP_CLI::confirm( 'Really REALLY sure?  This is your last chance!' );
		}

		if ( ! file_exists( $filename ) ) {
			WP_CLI::error( sprintf( "Missing file: %s", $filename ) );
		}

		global $wpdb;

		$n = 0;
		foreach ( new \WP_CLI\Iterators\CSV( $filename ) as $new_user ) {
			if ( ++$n % 10 === 0 ) {
				WP_CLI::line( sprintf( 'Processed users: %d', $n ) );
			}

			// WordPress _really_ doesn't like changing user IDs. We have to do this manually via a query.
			$old_user_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID from {$wpdb->users} WHERE {$user_key} = %s", $new_user[ $user_key ] ) );
			$old_user_id = absint( $old_user_id );
			if ( 0 === $old_user_id ) {
				WP_CLI::warning( sprintf( 'Skipped %s as we could not find existing user entry.', $new_user[ $user_key ] ) );
				continue;
			}

			$update_users = $wpdb->prepare( "UPDATE {$wpdb->users} SET ID = %d WHERE ID = %d LIMIT 1", $new_user['ID'], $old_user_id );
			$update_usermeta = $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET user_id = %d WHERE user_id = %d", $new_user['ID'], $old_user_id );

			if ( ! $dry_run ) {
				if ( false === $wpdb->query( $update_users ) ) {
					WP_CLI::warning( 'User ' . $new_user[ $user_key ] . ' ID NOT updated to ' . $new_user['ID'] );
					continue;
				}
				if ( false === $wpdb->query( $update_usermeta ) ) {
					WP_CLI::warning( 'User\'s ' . $new_user[ $user_key ] . ' meta was NOT updated to ' . $new_user['ID'] );
					continue;
				}
				WP_CLI::line( 'User ' . $new_user[ $user_key ] . ' was updated to ' . $new_user['ID'] );
			} else {
				WP_CLI::line( $update_users );
				WP_CLI::line( $update_usermeta );
			}
		}
		if ( ! $dry_run ) {
			wp_cache_flush();
			WP_CLI::line( 'Object cache flushed.' );
		} else {
			WP_CLI::line( 'Skipping object cache flush.' );
		}
		WP_CLI::success( 'All users in the file have been processed!' );
	}
}

WP_CLI::add_command( 'wpcom-compat', new WPCOM_Compat_Command );
