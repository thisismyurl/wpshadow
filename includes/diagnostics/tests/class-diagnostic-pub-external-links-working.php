<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic for checking if external links in published content are working.
 *
 * Scans a sample of published posts for external links and tests if they are reachable.
 * Helps maintain content quality and user experience by identifying broken external links.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */
class Diagnostic_Pub_External_Links_Working extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pub-external-links-working';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'External Links Working';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if external links in published content are reachable and not broken.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publishing';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Content Publishing';

	/**
	 * Get diagnostic ID.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic ID.
	 */
	public static function get_id(): string {
		return 'pub-external-links-working';
	}

	/**
	 * Get diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'External Links Working', 'wpshadow' );
	}

	/**
	 * Get diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Do all external links resolve?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic category.
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level.
	 *
	 * External links that don't work harm user experience and SEO.
	 * Medium severity as this affects content quality but not security.
	 *
	 * @since  1.2601.2148
	 * @return int Severity level (0-100).
	 */
	public static function get_threat_level(): int {
		return 40;
	}

	/**
	 * Run diagnostic test.
	 *
	 * This method is for backward compatibility.
	 * The actual check is performed in the check() method.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results.
	 */
	public static function run(): array {
		$finding = self::check();

		if ( null === $finding ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'All external links are working correctly', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $finding['description'],
			'data'    => $finding,
		);
	}

	/**
	 * Get KB article URL.
	 *
	 * @since  1.2601.2148
	 * @return string KB article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-external-links-working';
	}

	/**
	 * Get training video URL.
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Check for broken external links in published content.
	 *
	 * Scans a sample of published posts for external links and tests if they are reachable.
	 * Only checks a limited number of posts and links to avoid performance issues.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if broken links found, null otherwise.
	 */
	public static function check(): ?array {
		// Get a sample of recently published posts.
		$posts = get_posts(
			array(
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'post_type'      => array( 'post', 'page' ),
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$broken_links = array();
		$links_tested = 0;
		$max_links    = 20; // Limit total links tested to avoid performance issues.

		foreach ( $posts as $post_id ) {
			if ( $links_tested >= $max_links ) {
				break;
			}

			$content = get_post_field( 'post_content', $post_id );
			if ( empty( $content ) ) {
				continue;
			}

			// Extract external links from content.
			$external_links = self::extract_external_links( $content );

			foreach ( $external_links as $link ) {
				if ( $links_tested >= $max_links ) {
					break;
				}

				// Test if the link is reachable.
				$is_working = self::test_external_link( $link );

				if ( ! $is_working ) {
					$broken_links[] = array(
						'url'     => $link,
						'post_id' => $post_id,
					);
				}

				++$links_tested;
			}
		}

		// If broken links found, return a finding.
		if ( ! empty( $broken_links ) ) {
			$broken_count = count( $broken_links );
			$description  = sprintf(
				/* translators: %d: number of broken external links */
				__( 'Found %d broken external link(s) in your published content. Broken links harm user experience and SEO. Consider updating or removing these links.', 'wpshadow' ),
				$broken_count
			);

			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-external-links-working',
				__( 'Broken External Links Detected', 'wpshadow' ),
				$description,
				'publishing',
				'medium',
				40,
				'pub-external-links-working'
			);
		}

		return null;
	}

	/**
	 * Extract external links from HTML content.
	 *
	 * Uses WordPress's DOMDocument for safe HTML parsing to avoid ReDoS attacks.
	 *
	 * @since  1.2601.2148
	 * @param  string $content HTML content to parse.
	 * @return array Array of external URLs.
	 */
	private static function extract_external_links( string $content ): array {
		$external_links = array();
		$site_url       = home_url();
		$site_host      = wp_parse_url( $site_url, PHP_URL_HOST );

		// Suppress warnings from malformed HTML.
		libxml_use_internal_errors( true );

		// Use DOMDocument for safe HTML parsing.
		$dom = new \DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		$links = $dom->getElementsByTagName( 'a' );

		foreach ( $links as $link ) {
			$url = $link->getAttribute( 'href' );

			if ( empty( $url ) ) {
				continue;
			}

			// Skip anchors, javascript, mailto, tel, etc.
			$excluded_prefixes = array( '#', 'javascript:', 'mailto:', 'tel:', 'data:' );
			$should_skip       = false;

			foreach ( $excluded_prefixes as $prefix ) {
				if ( 0 === strpos( $url, $prefix ) ) {
					$should_skip = true;
					break;
				}
			}

			if ( $should_skip ) {
				continue;
			}

			// Skip relative URLs.
			if ( 0 !== strpos( $url, 'http' ) ) {
				continue;
			}

			// Check if URL is external.
			$url_host = wp_parse_url( $url, PHP_URL_HOST );
			if ( $url_host && $url_host !== $site_host ) {
				$external_links[] = $url;
			}
		}

		// Clear libxml errors.
		libxml_clear_errors();

		return array_unique( $external_links );
	}

	/**
	 * Test if an external link is working.
	 *
	 * Uses wp_remote_head() for efficiency, falls back to wp_remote_get() if needed.
	 * Tries with SSL verification first, falls back without SSL verification if that fails.
	 *
	 * @since  1.2601.2148
	 * @param  string $url URL to test.
	 * @return bool True if link is working, false otherwise.
	 */
	private static function test_external_link( string $url ): bool {
		// Try HEAD request with SSL verification first.
		$response = wp_remote_head(
			$url,
			array(
				'timeout'     => 3,
				'redirection' => 5,
				'user-agent'  => 'WPShadow-Diagnostic/1.0 (Link Checker)',
				'sslverify'   => true,
			)
		);

		// Check if request succeeded.
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			// Consider 2xx and 3xx status codes as working.
			if ( $status_code >= 200 && $status_code < 400 ) {
				return true;
			}
		}

		// If HEAD with SSL failed, try HEAD without SSL verification.
		$response = wp_remote_head(
			$url,
			array(
				'timeout'     => 3,
				'redirection' => 5,
				'user-agent'  => 'WPShadow-Diagnostic/1.0 (Link Checker)',
				'sslverify'   => false,
			)
		);

		// Check if request succeeded.
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			if ( $status_code >= 200 && $status_code < 400 ) {
				return true;
			}
		}

		// If HEAD requests failed, try GET with SSL verification (some servers don't support HEAD).
		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => 3,
				'redirection' => 5,
				'user-agent'  => 'WPShadow-Diagnostic/1.0 (Link Checker)',
				'sslverify'   => true,
			)
		);

		// Check if request succeeded.
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			if ( $status_code >= 200 && $status_code < 400 ) {
				return true;
			}
		}

		// Last resort: GET without SSL verification.
		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => 3,
				'redirection' => 5,
				'user-agent'  => 'WPShadow-Diagnostic/1.0 (Link Checker)',
				'sslverify'   => false,
			)
		);

		// Check if request succeeded.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		// Consider 2xx and 3xx status codes as working.
		return $status_code >= 200 && $status_code < 400;
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Verifies that the check() method returns the correct result based on site state.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_pub_external_links_working(): array {
		$result = self::check();

		// If no finding, the test passes (all external links are working).
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'All external links are working correctly', 'wpshadow' ),
			);
		}

		// If finding returned, broken links were detected.
		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: %s: finding description */
				__( 'Broken external links detected: %s', 'wpshadow' ),
				$result['description']
			),
		);
	}
}
