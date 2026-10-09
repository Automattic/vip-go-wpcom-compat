<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Automattic\VIPGoWPCOMCompat\Tests
 */

declare( strict_types = 1 );

namespace Automattic\VIPGoWPCOMCompat\Tests;

use Yoast\WPTestUtils\WPIntegration;

require_once dirname( __DIR__ ) . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

$_tests_dir = WPIntegration\get_path_to_wp_test_dir();

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Load the plugin as an mu-plugin, which is how sites on VIP load it.
\tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require __DIR__ . '/platform-stubs.php';
		require dirname( __DIR__ ) . '/vip-go-wpcom-compat.php';
	}
);

/*
 * Bootstrap WordPress. This will also load the Composer autoload file, the PHPUnit Polyfills
 * and the custom autoloader for the TestCase and the mock object classes.
 */
WPIntegration\bootstrap_it();
