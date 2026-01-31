<?php
/**
 * Broken Internal Links Diagnostic
 *
 * Finds internal links pointing to 404 pages.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broken Internal Links Class
 *
 * Tests for broken internal links.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Broken_Internal_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'broken-internal-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Internal Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Finds internal links pointing to 404 pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$broken_links = self::scan_for_broken_links();
		
		if ( $broken_links['count'] > 0 ) {
			$severity = 'low';
			if ( $broken_links['count'] > 20 ) {
				$severity = 'medium';
			} elseif ( $broken_links['count'] > 50 ) {
				$severity = 'high';
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of broken links */
					__( '%d broken internal links found (wasting SEO authority, frustrating users)', 'wpshadow' ),
					$broken_links['count']
				),
				'severity'     => $severity,
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/broken-internal-links',
				'meta'         => array(
					'broken_link_count' => $broken_links['count'],
					'content_links'     => $broken_links['content_links'],
					'menu_links'        => $broken_links['menu_links'],
					'sample_urls'       => array_slice( $broken_links['urls'], 0, 10 ),
				),
			);
		}

		return null;
	}

	/**
	 * Scan for broken internal links.
	 *
	 * @since  1.26028.1905
	 * @return array Statistics about broken links.
	 */
	private static function scan_for_broken_links() {
		global $wpdb;

		$broken = array(
			'count'         => 0,
			'content_links' => 0,
			'menu_links'    => 0,
			'urls'          => array(),
		);

		$site_url = get_site_url();
		$parsed_site_url = wp_parse_url( $site_url );
		$site_host = isset( $parsed_site_url['host'] ) ? $parsed_site_url['host'] : '';

		// Scan post/page content (limited sample to avoid performance issues).
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_content
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				ORDER BY post_modified DESC
				LIMIT 50",
				'publish'
			)
		);

		foreach ( $posts as $post ) {
			// Find all internal links.
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches );
			
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					// Check if URL is internal.
					$parsed_url = wp_parse_url( $url );
					$is_internal = false;

					if ( ! isset( $parsed_url['host'] ) ) {
						$is_internal = true; // Relative URL.
					} elseif ( isset( $parsed_url['host'] ) && $parsed_url['host'] === $site_host ) {
						$is_internal = true;
					}

					if ( $is_internal && ! in_array( $url, $broken['urls'], true ) ) {
						// Test if URL returns 404.
						if ( self::is_broken_link( $url ) ) {
							++$broken['content_links'];
							++$broken['count'];
							$broken['urls'][] = $url;
						}
					}
				}
			}
		}

		// Check menu items.
		$menu_items = wp_get_nav_menu_items( get_nav_menu_locations() );
		if ( $menu_items ) {
			foreach ( $menu_items as $item ) {
				if ( ! empty( $item->url ) ) {
					$parsed_url = wp_parse_url( $item->url );
					$is_internal = false;

					if ( ! isset( $parsed_url['host'] ) || 
						 ( isset( $parsed_url['host'] ) && $parsed_url['host'] === $site_host ) ) {
						$is_internal = true;
					}

					if ( $is_internal && ! in_array( $item->url, $broken['urls'], true ) ) {
						if ( self::is_broken_link( $item->url ) ) {
							++$broken['menu_links'];
							++$broken['count'];
							$broken['urls'][] = $item->url;
						}
					}
				}
			}
		}

		return $broken;
	}

	/**
	 * Test if a URL returns 404.
	 *
	 * @since  1.26028.1905
	 * @param  string $url URL to test.
	 * @return bool True if link is broken.
	 */
	private static function is_broken_link( $url ) {
		// Convert relative URLs to absolute.
		if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
			$url = get_site_url() . $url;
		}

		// Use WordPress HTTP API.
		$response = wp_remote_head( $url, array( 'timeout' => 5, 'redirection' => 5 ) );
		
		if ( is_wp_error( $response ) ) {
			return true; // Can't reach URL, consider broken.
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		return 404 === $status_code;
	}
}
