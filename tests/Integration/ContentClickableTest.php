<?php
/**
 * Tests for wpcom_make_content_clickable().
 *
 * @package Automattic\VIPGoWPCOMCompat\Tests
 */

declare( strict_types = 1 );

namespace Automattic\VIPGoWPCOMCompat\Tests\Integration;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Plain-text URLs in content become links, as they did on WordPress.com.
 */
final class ContentClickableTest extends TestCase {

	public function test_is_hooked_to_content_and_excerpt_at_priority_120(): void {
		$this->assertSame( 120, has_filter( 'the_content', 'wpcom_make_content_clickable' ) );
		$this->assertSame( 120, has_filter( 'the_excerpt', 'wpcom_make_content_clickable' ) );
	}

	public function test_links_a_plain_text_url(): void {
		$this->assertSame(
			'<p>See <a href="https://example.com" rel="nofollow">https://example.com</a> now</p>',
			wpcom_make_content_clickable( '<p>See https://example.com now</p>' )
		);
	}

	/**
	 * Content inside these elements must not be linked.
	 *
	 * @return array<string, array{string}>
	 */
	public function data_skipped_elements(): array {
		return array(
			'existing link'           => array( '<a href="https://example.org">https://example.com</a>' ),
			'pre'                     => array( '<pre>https://example.com</pre>' ),
			'script'                  => array( '<script>var u = "https://example.com";</script>' ),
			'style'                   => array( '<style>/* https://example.com */</style>' ),
			'textarea'                => array( '<textarea>https://example.com</textarea>' ),
			'skip-make-clickable div' => array( '<div class="skip-make-clickable">https://example.com</div>' ),
		);
	}

	/**
	 * @dataProvider data_skipped_elements
	 *
	 * @param string $content Content that should pass through untouched.
	 */
	public function test_leaves_content_inside_skipped_elements_alone( string $content ): void {
		$this->assertSame( $content, wpcom_make_content_clickable( $content ) );
	}

	public function test_leaves_content_without_urls_alone(): void {
		$this->assertSame( '<p>No links here.</p>', wpcom_make_content_clickable( '<p>No links here.</p>' ) );
	}
}
