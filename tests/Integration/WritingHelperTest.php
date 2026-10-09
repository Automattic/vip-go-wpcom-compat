<?php
/**
 * Tests for the access checks in the bundled Writing Helper plugin.
 *
 * @package Automattic\VIPGoWPCOMCompat\Tests
 */

declare( strict_types = 1 );

namespace Automattic\VIPGoWPCOMCompat\Tests\Integration;

use Writer_Helper_Copy_Post;
use Writing_Helper_Draft_Feedback;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Draft share links and Copy a Post should only reveal what the user is allowed to see.
 */
final class WritingHelperTest extends TestCase {

	public function tear_down(): void {
		unset( $_REQUEST['shareadraft'], $_GET['shareadraft'], $_GET['nux'] );
		parent::tear_down();
	}

	public function test_share_keys_are_long_random_and_unique(): void {
		$first  = Writing_Helper_Draft_Feedback::generate_key();
		$second = Writing_Helper_Draft_Feedback::generate_key();

		$this->assertMatchesRegularExpression( '/^[A-Za-z0-9]{32}$/', $first );
		$this->assertNotSame( $first, $second );
	}

	/**
	 * Keys a share link should, and shouldn't, be accepted with.
	 *
	 * @return array<string, array{mixed, bool}>
	 */
	public function data_share_keys(): array {
		return array(
			'exact new-style key'    => array( 'Abc123Def456Ghi789Jkl012Mno345Pq', true ),
			'existing uniqid() key'  => array( '64f1a2b3c4d5e', true ),
			'neighbouring uniqid()'  => array( '64f1a2b3c4d5f', false ),
			'revoked key'            => array( '64f1a2b3c4d60', false ),
			'array instead of a key' => array( array( '64f1a2b3c4d5e' ), false ),
			// '6512300000000' is a valid uniqid(), and PHP's == treats it as the same number as '6.5123e12'.
			'numerically equal key'  => array( '6.5123e12', false ),
		);
	}

	/**
	 * @dataProvider data_share_keys
	 *
	 * @param mixed $key      Key from the request.
	 * @param bool  $expected Whether it should grant access.
	 */
	public function test_share_link_access( $key, bool $expected ): void {
		$post_id = self::factory()->post->create( array( 'post_status' => 'draft' ) );
		update_post_meta(
			$post_id,
			Writing_Helper_Draft_Feedback::requests_metakey,
			array(
				'new@example.com'     => array( 'key' => 'Abc123Def456Ghi789Jkl012Mno345Pq' ),
				'old@example.com'     => array( 'key' => '64f1a2b3c4d5e' ),
				'numeric@example.com' => array( 'key' => '6512300000000' ),
				'revoked@example.com' => array(
					'key'     => '64f1a2b3c4d60',
					'revoked' => true,
				),
			)
		);

		$_REQUEST['shareadraft'] = $key;

		$this->assertSame( $expected, ( new Writing_Helper_Draft_Feedback() )->can_view( $post_id ) );
	}

	public function test_contributor_can_only_copy_published_posts_and_their_own_drafts(): void {
		$contributor = self::factory()->user->create( array( 'role' => 'contributor' ) );
		$author      = self::factory()->user->create( array( 'role' => 'author' ) );

		$published   = self::factory()->post->create( array( 'post_author' => $author ) );
		$own_draft   = self::factory()->post->create(
			array(
				'post_author' => $contributor,
				'post_status' => 'draft',
			)
		);
		$other_draft = self::factory()->post->create(
			array(
				'post_author' => $author,
				'post_status' => 'draft',
			)
		);
		$protected   = self::factory()->post->create(
			array(
				'post_author'   => $author,
				'post_password' => 'secret',
			)
		);
		wp_set_current_user( $contributor );

		$ids = wp_list_pluck( Writer_Helper_Copy_Post::get_candidate_posts( 'post' ), 'ID' );

		$this->assertContains( $published, $ids );
		$this->assertContains( $own_draft, $ids );
		$this->assertNotContains( $other_draft, $ids );
		$this->assertNotContains( $protected, $ids );
	}

	public function test_editor_can_copy_password_protected_posts(): void {
		$protected = self::factory()->post->create_and_get( array( 'post_password' => 'secret' ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		$this->assertTrue( Writer_Helper_Copy_Post::can_copy( $protected ) );
	}

	public function test_post_types_without_writing_helper_support_are_not_searched(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		self::factory()->post->create( array( 'post_type' => 'attachment' ) );

		$this->assertSame( array(), Writer_Helper_Copy_Post::get_candidate_posts( 'attachment' ) );
	}

	public function test_post_password_is_removed_before_output(): void {
		$post = self::factory()->post->create_and_get( array( 'post_password' => 'secret' ) );

		$this->assertSame( '', Writer_Helper_Copy_Post::prepare_for_output( $post )->post_password );
	}
}
