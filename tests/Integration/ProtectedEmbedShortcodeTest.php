<?php
/**
 * Tests for the [protected-iframe] shortcode.
 *
 * @package Automattic\VIPGoWPCOMCompat\Tests
 */

declare( strict_types = 1 );

namespace Automattic\VIPGoWPCOMCompat\Tests\Integration;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Embeds imported from WordPress.com should still render.
 */
final class ProtectedEmbedShortcodeTest extends TestCase {

	private const TABLE = 'wptests_protected_embeds_test';

	public function set_up(): void {
		parent::set_up();

		global $wpdb;
		// The test framework turns this into a temporary table, so it lasts until the connection closes.
		$wpdb->query( 'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' ( embed_id varchar(64) NOT NULL, html mediumtext )' ); // phpcs:ignore WordPress.DB
		$wpdb->insert( self::TABLE, array( 'embed_id' => 'abc123', 'html' => '<iframe src="https://example.com"></iframe>' ) ); // phpcs:ignore WordPress.DB

		add_filter( 'wpcom_protected_embed_table', array( $this, 'table_name' ) );
	}

	/**
	 * @return string Test table name.
	 */
	public function table_name(): string {
		return self::TABLE;
	}

	public function test_renders_a_stored_embed(): void {
		$this->assertSame( '<iframe src="https://example.com"></iframe>', do_shortcode( '[protected-iframe id="abc123"]' ) );
	}

	public function test_unknown_id_renders_the_not_found_comment(): void {
		$this->assertSame( '<!-- Embed not found -->', do_shortcode( '[protected-iframe id="nope"]' ) );
	}

	public function test_missing_id_renders_the_not_found_comment(): void {
		$this->assertSame( '<!-- Embed not found -->', do_shortcode( '[protected-iframe]' ) );
	}
}
