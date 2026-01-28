<?php
/**
 * 404 Error Rate Diagnostic
 *
 * Measures percentage of page requests resulting in 404 errors, indicating
 * broken links or content issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since      1.6028.2115
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 404 Error Rate Diagnostic Class
 *
 * Analyzes site 404 error rate by checking common URLs and internal links.
 * High 404 rates indicate broken links, missing content, or redirect issues.
 *
 * @since 1.6028.2115
 */
class Diagnostic_404_Error_Rate extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = '404-error-rate';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = '404 Error Rate Above 5%';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures 404 error rate by testing internal links and common URLs';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.2115
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cache_key = 'wpshadow_404_error_rate_check';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Collect URLs to test.
		$urls = self::collect_test_urls();

		// Test each URL.
		$results = self::test_urls( $urls );

		// Calculate error rate.
		$total_urls  = count( $results );
		$error_count = count(
			array_filter(
				$results,
				function( $result ) {
					return 404 === $result['status'];
				}
			)
		);

		$error_rate = $total_urls > 0 ? ( $error_count / $total_urls ) * 100 : 0;

		// Determine severity.
		if ( $error_rate < 1 ) {
			$result = null; // Excellent, no issue.
		} elseif ( $error_rate < 5 ) {
			$result = null; // Good, within acceptable range.
		} else {
			// Warning or critical - create finding.
			$severity     = $error_rate > 10 ? 'high' : 'medium';
			$threat_level = $error_rate > 10 ? 70 : 50;

			$error_urls = array_filter(
				$results,
				function( $result ) {
					return 404 === $result['status'];
				}
			);

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: error rate percentage */
					__( '404 error rate is %.1f%%, indicating broken links or missing content', 'wpshadow' ),
					$error_rate
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ux-404-error-rate',
				'family'       => self::$family,
				'meta'         => array(
					'total_urls_tested' => $total_urls,
					'error_count'       => $error_count,
					'error_rate'        => round( $error_rate, 2 ),
					'thresholds'        => array(
						'excellent' => 1.0,
						'good'      => 5.0,
						'warning'   => 10.0,
					),
				),
				'details'      => array(
					'error_urls' => array_slice( array_values( $error_urls ), 0, 15 ),
				),
				'recommendations' => array(
					__( 'Fix broken internal links pointing to missing pages', 'wpshadow' ),
					__( 'Set up proper 301 redirects for moved or deleted content', 'wpshadow' ),
					__( 'Review and update navigation menus with broken links', 'wpshadow' ),
					__( 'Check for typos in redirect rules or rewrite rules', 'wpshadow' ),
					__( 'Use a broken link checker plugin to identify issues', 'wpshadow' ),
				),
			);
		}

		// Cache for 12 hours.
		set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Collect URLs to test for 404 errors.
	 *
	 * @since  1.6028.2115
	 * @return array Array of URLs to test.
	 */
	private static function collect_test_urls() {
		$urls = array();

		// Get homepage.
		$urls[] = home_url( '/' );

		// Get recent posts.
		$posts = get_posts(
			array(
				'numberposts' => 10,
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);

		foreach ( $posts as $post ) {
			$urls[] = get_permalink( $post->ID );
		}

		// Get recent pages.
		$pages = get_posts(
			array(
				'numberposts' => 5,
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);

		foreach ( $pages as $page ) {
			$urls[] = get_permalink( $page->ID );
		}

		// Extract internal links from homepage.
		$internal_links = self::extract_internal_links_from_homepage();
		$urls           = array_merge( $urls, array_slice( $internal_links, 0, 20 ) );

		// Test some common URLs that might be broken.
		$common_urls = array(
			'/blog/',
			'/about/',
			'/contact/',
			'/wp-content/uploads/test.jpg',
			'/category/test/',
			'/tag/test/',
		);

		foreach ( $common_urls as $path ) {
			$urls[] = home_url( $path );
		}

		return array_unique( $urls );
	}

	/**
	 * Extract internal links from homepage.
	 *
	 * @since  1.6028.2115
	 * @return array Array of internal URLs.
	 */
	private static function extract_internal_links_from_homepage() {
		$response = wp_remote_get( home_url( '/' ) );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$html = wp_remote_retrieve_body( $response );
		$home = home_url( '/' );

		// Extract href attributes.
		preg_match_all( '/href=["\']([^"\']+)["\']/i', $html, $matches );

		$links = array();
		foreach ( $matches[1] as $url ) {
			// Filter for internal links only.
			if ( strpos( $url, $home ) === 0 || strpos( $url, '/' ) === 0 ) {
				// Convert relative to absolute.
				if ( strpos( $url, '/' ) === 0 && strpos( $url, '//' ) !== 0 ) {
					$url = home_url( $url );
				}
				$links[] = $url;
			}
		}

		return array_unique( $links );
	}

	/**
	 * Test URLs for 404 errors.
	 *
	 * @since  1.6028.2115
	 * @param  array $urls URLs to test.
	 * @return array Array of results with URL and status code.
	 */
	private static function test_urls( $urls ) {
		$results = array();

		foreach ( $urls as $url ) {
			$response = wp_remote_head(
				$url,
				array(
					'timeout'     => 5,
					'redirection' => 5,
					'sslverify'   => false, // Allow self-signed certs in dev.
				)
			);

			if ( is_wp_error( $response ) ) {
				// Connection error, skip.
				continue;
			}

			$status_code = wp_remote_retrieve_response_code( $response );

			$results[] = array(
				'url'    => $url,
				'status' => $status_code,
			);
		}

		return $results;
	}
}
