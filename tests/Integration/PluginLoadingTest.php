<?php
/**
 * Tests for the plugin loading helpers.
 *
 * @package Automattic\VIPGoWPCOMCompat\Tests
 */

declare( strict_types = 1 );

namespace Automattic\VIPGoWPCOMCompat\Tests\Integration;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Sites still calling wpcom_vip_legacy_load_plugin() break if its mapping changes.
 */
final class PluginLoadingTest extends TestCase {

	/**
	 * WordPress.com style arguments, and what VIP's loader should receive.
	 *
	 * @return array<string, array{array<int, string|false>, array{string|false, string|false}}>
	 */
	public function data_legacy_load_plugin(): array {
		return array(
			'unversioned'                => array( array( 'my-plugin' ), array( 'my-plugin', false ) ),
			'versioned, plugins folder'  => array( array( 'my-plugin', 'plugins', '2.0' ), array( 'my-plugin-2.0/my-plugin.php', false ) ),
			'versioned, theme folder'    => array( array( 'my-plugin', 'theme', '2.0' ), array( 'my-plugin-2.0/my-plugin.php', false ) ),
			'unversioned, custom folder' => array( array( 'my-plugin', 'shared-plugins' ), array( 'my-plugin', 'shared-plugins' ) ),
		);
	}

	/**
	 * @dataProvider data_legacy_load_plugin
	 *
	 * @param array<int, string|false>           $args     Arguments as used on WordPress.com.
	 * @param array{string|false, string|false} $expected Arguments passed on to wpcom_vip_load_plugin().
	 */
	public function test_legacy_load_plugin_maps_to_vip_loader( array $args, array $expected ): void {
		$this->assertTrue( wpcom_vip_legacy_load_plugin( ...$args ) );
		$this->assertSame( $expected, $GLOBALS['wpcom_vip_load_plugin_args'] );
	}

	public function test_plugins_url_resolves_files_inside_a_theme(): void {
		$file = WP_CONTENT_DIR . '/themes/my-theme/plugins/my-plugin/my-plugin.php';

		$this->assertSame(
			content_url( 'themes/my-theme/plugins/my-plugin/js/script.js' ),
			plugins_url( 'js/script.js', $file )
		);
	}

	public function test_plugins_url_is_unchanged_outside_themes(): void {
		$file = WP_PLUGIN_DIR . '/my-plugin/my-plugin.php';

		$this->assertSame( WP_PLUGIN_URL . '/my-plugin/js/script.js', plugins_url( 'js/script.js', $file ) );
	}

	public function test_bundled_plugins_are_loaded(): void {
		$this->assertTrue( class_exists( 'Writing_Helper' ), 'Writing Helper should be loaded.' );
		$this->assertTrue( function_exists( 'mrss_init' ), 'MediaRSS should be loaded.' );
	}

	public function test_jetpack_sso_is_forced_on_and_matches_by_email(): void {
		$this->assertTrue( apply_filters( 'jetpack_sso_match_by_email', false ) );
		$this->assertSame( array( 'stats', 'sso' ), array_values( apply_filters( 'jetpack_active_modules', array( 'stats', 'sso' ) ) ) );
		$this->assertContains( 'sso', apply_filters( 'jetpack_active_modules', array( 'stats' ) ) );
	}
}
