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
	 * Import theme options, sidebars options and widgets options from a JSON file.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : The JSON file with all the settings.
	 *
	 * [--dry-run=<true>]
	 * : Do a "dry run" and no data modification will be done.  Defaults to true.
	 *
	 * [--yes]
	 * : Skip the confirmation prompt
	 *
	 * ## EXAMPLES
	 *
	 *     # Generates the JSON file with theme options (on WordPress.com sandbox)
	 *     $ wp vip-export theme-options --url=example.com --filename=file.json
	 *
	 *     # Copies theme options from the JSON file.
	 *     $ wp wpcom-compat import-theme-options file.json
	 *
	 *
	 * @subcommand import-theme-options
	 */
	function import_theme_options($args, $assoc_args) {
		$dry_run = WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', true );
		$filename = $args[0];
		// Force a boolean, always default to true.
		$dry_run = filter_var( $dry_run, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? true;
		if ( $dry_run ) {
			WP_CLI::warning( 'Performing a dry run, with no database modification.' );
		}
		$current_theme = get_option( 'stylesheet' );
		if ( ! file_exists( $filename ) || ( $json = file_get_contents( $filename ) ) === false ) {
			WP_CLI::error( "The provided file does not exist or couldn't be open." );
		}
		WP_CLI::log( "$filename is a valid file. Parsing the content..." );
		$options = json_decode( $json, true );
		if( ! $options || ! ( is_array( $options['theme'] ) && is_array( $options['sidebar'] ) && is_array( $options['widgets'] ) ) ) {
			WP_CLI::error( "The file $filename is not a valid JSON file." );
		}
		WP_CLI::line( "ATTENTION! This operation is not reversible. It will override all the current existent widgets, sidebar, and active theme options. Please make sure the following data is correct before proceeding. \n" );
		$widget_names = wp_list_pluck( $options['widgets'], 'name' );
		$sidebar_display = "";
		foreach( $options['sidebar'] as $sidebar_name => $sidebar_widgets ) {
			$sidebar_display .= WP_CLI::colorize( " %9* $sidebar_name%n: " );
			$sidebar_display .= implode( $sidebar_widgets, ", ") . "\n";
		}
		WP_CLI::log( WP_CLI::colorize(
			"%9Current Site:%n " . get_home_url( ) . "\n" .
			"%9Current Theme:%n " . $current_theme . "\n" .
			"%9Widgets being replaced/created:%n " . implode( $widget_names,", " ) . "\n" .
			"%9Sidebar and Widget Positions:%n\n" . $sidebar_display
		) );
		WP_CLI::confirm( "Are you sure you want to continue?" , $assoc_args );
		/* Updates Theme Options */
		WP_CLI::line( "\n * Updating Theme options (`theme_mods_$current_theme`)" );
		if ( $dry_run ) {
			WP_CLI::line( "[DRY-RUN] Should update `theme_mods_$current_theme`" );
		} else {
			$update = update_option( "theme_mods_$current_theme", $options['theme'] );
			if ( ! $update ) {
				WP_CLI::warning( "The option `theme_mods_$current_theme` wasn't updated. Perhaps there is nothing to update (the current theme might be already with the original theme options). Continuing." );
			} else {
				WP_CLI::success( "Option `theme_mods_$current_theme` has been updated." );
			}
		}
		/* Updates Sidebar Options */
		WP_CLI::line( " * Updating Sidebar Options" );
		if ( $dry_run ) {
			WP_CLI::line( "[DRY-RUN] Should update `sidebars_options`" );
		} else {
			wp_set_sidebars_widgets( $options['sidebar'] );
			WP_CLI::success( "Option `sidebars_options` has been updated." );
		}
		/* Updates Widgets Options */
		WP_CLI::line(" * Updating widgets Options");
		foreach( $options['widgets'] as $widget ) {
			$widget_name = $widget['name'];
			$widget_value = $widget['value'];
			if ( $dry_run ) {
				WP_CLI::line( "[DRY-RUN] Should update `$widget_name`" );
			} else {
				$update = update_option( $widget_name, $widget_value );
				if ( ! $update ) {
					WP_CLI::warning( "The option `$widget_name` wasn't updated. Perhaps there is nothing to update. Continuing." );
					continue;
				}
				WP_CLI::success( "Option `$widget_name` has been updated" );
			}
		}
		// Flushing immediately is required to be sure that the options will not be poisoned by a bad cache
		WP_CLI::line( "Flushing cache." );
		wp_cache_flush();
		WP_CLI::success( "Done!" );
	}

}

WP_CLI::add_command( 'wpcom-compat', new WPCOM_Compat_Command );
