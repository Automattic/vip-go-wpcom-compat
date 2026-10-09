<?php
/**
 * Tests for the WordPress.com sitemaps.
 *
 * @package Automattic\VIPGoWPCOMCompat\Tests
 */

declare( strict_types = 1 );

namespace Automattic\VIPGoWPCOMCompat\Tests\Integration;

use SimpleXMLElement;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Both sitemap functions end with die(), so these tests throw from one of the plugin's own
 * filters to stop just before that, and inspect what had been built or printed by then.
 */
final class SitemapTest extends TestCase {

	public function test_sitemap_lists_published_posts_pages_and_home(): void {
		$post_id  = self::factory()->post->create();
		$page_id  = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$draft_id = self::factory()->post->create( array( 'post_status' => 'draft' ) );

		$xml  = $this->get_sitemap();
		$urls = $this->urls_by_loc( $xml );

		$this->assertArrayHasKey( get_permalink( $post_id ), $urls );
		$this->assertArrayHasKey( get_permalink( $page_id ), $urls );
		$this->assertArrayNotHasKey( get_permalink( $draft_id ), $urls );

		$post = $urls[ get_permalink( $post_id ) ];
		$this->assertSame( 'monthly', (string) $post->changefreq );
		$this->assertSame( '', (string) $post->priority );
		$this->assertCount( 1, $post->children( 'http://www.google.com/schemas/sitemap-mobile/1.0' ) );

		$page = $urls[ get_permalink( $page_id ) ];
		$this->assertSame( 'weekly', (string) $page->changefreq );
		$this->assertSame( '0.6', (string) $page->priority );

		$home = $urls[ esc_url( get_option( 'home' ) ) ];
		$this->assertSame( 'daily', (string) $home->changefreq );
		$this->assertSame( '1.0', (string) $home->priority );
		$this->assertSame( (string) $home->lastmod, max( (string) $post->lastmod, (string) $page->lastmod ) );
	}

	public function test_sitemap_lastmod_is_the_post_modified_date_in_w3c_format(): void {
		$post_id = self::factory()->post->create();
		$this->set_post_modified_gmt( $post_id, '2026-01-02 03:04:05' );

		$urls = $this->urls_by_loc( $this->get_sitemap() );

		$this->assertSame( '2026-01-02T03:04:05+00:00', (string) $urls[ get_permalink( $post_id ) ]->lastmod );
	}

	public function test_sitemap_includes_one_image_from_a_posts_jpeg_attachments(): void {
		$post_id = self::factory()->post->create();
		for ( $i = 1; $i <= 6; $i++ ) {
			self::factory()->attachment->create(
				array(
					'post_parent'    => $post_id,
					'post_mime_type' => 'image/jpeg',
					'post_title'     => "Image $i",
					'guid'           => "https://example.com/image-$i.jpg",
				)
			);
		}

		$urls  = $this->urls_by_loc( $this->get_sitemap() );
		$image = $urls[ get_permalink( $post_id ) ]->children( 'http://www.google.com/schemas/sitemap-image/1.1' )->image;

		// Each attachment overwrites the same image:image element, so only the last one shows.
		$this->assertCount( 1, $image );
		$this->assertStringStartsWith( 'https://example.com/image-', (string) $image->loc );
		$this->assertStringStartsWith( 'Image ', (string) $image->title );
	}

	public function test_sitemap_skip_post_filter_removes_a_post(): void {
		$post_id = self::factory()->post->create();
		add_filter(
			'sitemap_skip_post',
			static function ( $skip, $post ) use ( $post_id ) {
				return (int) $post->ID === $post_id;
			},
			10,
			2
		);

		$this->assertArrayNotHasKey( get_permalink( $post_id ), $this->urls_by_loc( $this->get_sitemap() ) );
	}

	public function test_news_sitemap_prints_recent_posts(): void {
		$tag_post_id = self::factory()->post->create(
			array(
				'post_title' => 'Tagged news',
				'post_date'  => gmdate( 'Y-m-d H:i:s', time() - HOUR_IN_SECONDS ),
				'tags_input' => array( 'alpha', 'beta' ),
			)
		);
		// Oldest, so printed last; the test stops on it, before die().
		$sentinel_id = self::factory()->post->create( array( 'post_date' => gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS ) ) );

		$output = $this->get_news_sitemap( $sentinel_id );
		$post   = get_post( $tag_post_id );

		$this->assertStringStartsWith(
			"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!-- generator=\"wordpress.com\" -->\n<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n",
			$output
		);
		$this->assertStringEndsWith(
			'<url><loc>' . esc_html( get_permalink( $tag_post_id ) ) . '</loc><news:news><news:publication><news:name>' . get_bloginfo_rss( 'name' ) . '</news:name></news:publication>'
			. '<news:publication_date>' . str_replace( ' ', 'T', $post->post_date_gmt ) . '+00:00</news:publication_date>'
			. '<news:title>Tagged news</news:title><news:keywords>alpha, beta</news:keywords></news:news></url>',
			$output
		);
	}

	/**
	 * Build the main sitemap, and return it without printing it.
	 *
	 * @return SimpleXMLElement The sitemap.
	 */
	private function get_sitemap(): SimpleXMLElement {
		add_filter(
			'wpcom_print_sitemap',
			static function ( $tree ) {
				throw new StopSitemap( $tree->asXML() );
			}
		);

		try {
			$this->ignoring_header_warnings( '\WPCOM_Sitemap\wpcom_print_sitemap' );
		} catch ( StopSitemap $e ) {
			return new SimpleXMLElement( $e->getMessage() );
		}

		$this->fail( 'The sitemap was not built.' );
	}

	/**
	 * Print the news sitemap up to, but not including, the given post.
	 *
	 * @param int $stop_at_post_id Post to stop at.
	 * @return string Output so far.
	 */
	private function get_news_sitemap( int $stop_at_post_id ): string {
		add_filter(
			'wpcom_sitemap_news_skip_post',
			static function ( $skip, $post ) use ( $stop_at_post_id ) {
				if ( (int) $post->ID === $stop_at_post_id ) {
					throw new StopSitemap();
				}
				return $skip;
			},
			10,
			2
		);

		ob_start();
		try {
			$this->ignoring_header_warnings( '\WPCOM_Sitemap\wpcom_print_news_sitemap', null );
		} catch ( StopSitemap $e ) {
			return (string) ob_get_clean();
		}
		ob_end_clean();

		$this->fail( 'The news sitemap did not reach the last post.' );
	}

	/**
	 * Call a function, ignoring the warnings from header() that PHPUnit's earlier output causes.
	 *
	 * @param callable $callback Function to call.
	 * @param mixed    ...$args  Arguments to pass to it.
	 */
	private function ignoring_header_warnings( callable $callback, ...$args ): void {
		set_error_handler(
			static function ( $errno, $errstr ) {
				return 0 === strpos( $errstr, 'Cannot modify header information' );
			},
			E_WARNING
		);

		try {
			$callback( ...$args );
		} finally {
			restore_error_handler();
		}
	}

	/**
	 * Index the sitemap's url elements by their loc.
	 *
	 * @param SimpleXMLElement $xml Sitemap.
	 * @return array<string, SimpleXMLElement>
	 */
	private function urls_by_loc( SimpleXMLElement $xml ): array {
		$urls = array();
		foreach ( $xml->url as $url ) {
			$urls[ (string) $url->loc ] = $url;
		}
		return $urls;
	}

	/**
	 * Set a post's modified date directly, as wp_insert_post() always uses the current time.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $modified Modified date, in GMT.
	 */
	private function set_post_modified_gmt( int $post_id, string $modified ): void {
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_modified_gmt' => $modified ), array( 'ID' => $post_id ) ); // phpcs:ignore WordPress.DB
		clean_post_cache( $post_id );
	}
}

/**
 * Thrown from a sitemap filter to stop before the sitemap code calls die().
 */
final class StopSitemap extends \Exception {} // phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
