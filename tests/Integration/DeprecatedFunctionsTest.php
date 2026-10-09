<?php
/**
 * Tests for the deprecated WordPress.com function shims.
 *
 * @package Automattic\VIPGoWPCOMCompat\Tests
 */

declare( strict_types = 1 );

namespace Automattic\VIPGoWPCOMCompat\Tests\Integration;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * The shims exist so that old theme code keeps running, so each must still be callable.
 */
final class DeprecatedFunctionsTest extends TestCase {

	/**
	 * Shims that only flag themselves as deprecated.
	 *
	 * @return array<string, array{string}>
	 */
	public function data_no_op_shims(): array {
		return array(
			'wpcom_vip_load_wp_rest_api'          => array( 'wpcom_vip_load_wp_rest_api' ),
			'wpcom_vip_enable_https_canonical'    => array( 'wpcom_vip_enable_https_canonical' ),
			'vip_goog_stats'                      => array( 'vip_goog_stats' ),
			'wpcom_vip_remove_mp6_styles'         => array( 'wpcom_vip_remove_mp6_styles' ),
			'wpcom_vip_remove_bbpress2_staff_css' => array( 'wpcom_vip_remove_bbpress2_staff_css' ),
			'wpcom_vip_enabled_cap_in_oembed'     => array( 'wpcom_vip_enabled_cap_in_oembed' ),
		);
	}

	/**
	 * @dataProvider data_no_op_shims
	 *
	 * @param string $function_name Shim to call.
	 */
	public function test_no_op_shim_is_callable_and_deprecated( string $function_name ): void {
		$this->setExpectedDeprecated( $function_name );

		$this->assertNull( $function_name() );
	}

	public function test_is_wpcom_vip_is_false_without_the_wpcom_constant(): void {
		$this->setExpectedDeprecated( 'is_wpcom_vip' );

		$this->assertFalse( is_wpcom_vip() );
	}

	public function test_protected_embed_to_original_returns_content_unchanged(): void {
		$this->setExpectedDeprecated( 'wpcom_vip_protected_embed_to_original' );

		$this->assertSame( '<p>Hello</p>', wpcom_vip_protected_embed_to_original( '<p>Hello</p>' ) );
	}

	public function test_require_lib_ignores_a_missing_library(): void {
		$this->setExpectedDeprecated( 'require_lib' );

		if ( ! defined( 'WPCOM_VIP_CLIENT_MU_PLUGIN_DIR' ) ) {
			define( 'WPCOM_VIP_CLIENT_MU_PLUGIN_DIR', WP_CONTENT_DIR . '/client-mu-plugins' );
		}

		$this->assertNull( require_lib( 'does-not-exist' ) );
	}
}
