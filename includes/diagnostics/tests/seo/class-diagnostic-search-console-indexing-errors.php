<?php
/**
 * Search Console Indexing Errors Diagnostic
 *
 * Queries Google Search Console to detect indexing errors preventing pages
 * from appearing in search results, affecting search visibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search Console Indexing Errors Diagnostic Class
 *
 * Detects indexing errors from Google Search Console that prevent
 * pages from being indexed and appearing in search results.
 *
 * @since 1.6028.1445
 */
class Diagnostic_Search_Console_Indexing_Errors extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'search-console-indexing-errors';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Search Console Indexing Errors Above 5%';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Queries Google Search Console to detect indexing errors preventing pages from search results';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cache_key = 'wpshadow_indexing_errors_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$result = self::check_indexing_errors();

		// Cache for 24 hours.
		set_transient( $cache_key, $result, DAY_IN_SECONDS );

		return $result;
	}

	/**
	 * Check for indexing errors.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	private static function check_indexing_errors() {
		// Try to get indexing data from available sources.
		$indexing_data = self::get_indexing_data();

		if ( empty( $indexing_data ) ) {
			// No indexing data available - check for basic indexability issues.
			return self::check_basic_indexability();
		}

		$total_pages   = $indexing_data['total_pages'] ?? 0;
		$error_pages   = $indexing_data['error_pages'] ?? 0;
		$excluded_pages = $indexing_data['excluded_pages'] ?? 0;

		if ( $total_pages <= 0 ) {
			return null;
		}

		// Calculate error percentage.
		$error_pct = ( $error_pages / $total_pages ) * 100;

		// Check thresholds.
		if ( $error_pct < 1 ) {
			return null; // <1% is excellent.
		} elseif ( $error_pct < 5 ) {
			// Between 1-5% - acceptable but worth monitoring.
			return null;
		}

		$severity     = $error_pct;
		$threat_level = self::calculate_threat_level( $error_pct );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: error percentage, 2: number of error pages, 3: total pages */
				__( '%1$.1f%% of pages have indexing errors (%2$d errors out of %3$d pages)', 'wpshadow' ),
				$error_pct,
				$error_pages,
				$total_pages
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/search-console-indexing-errors',
			'meta'         => array(
				'total_pages'     => $total_pages,
				'error_pages'     => $error_pages,
				'excluded_pages'  => $excluded_pages,
				'indexed_pages'   => $indexing_data['indexed_pages'] ?? 0,
				'error_percent'   => $error_pct,
				'data_source'     => $indexing_data['source'] ?? 'unknown',
				'error_types'     => $indexing_data['error_types'] ?? array(),
			),
			'details'      => self::get_error_details( $indexing_data ),
			'recommendations' => self::get_recommendations( $indexing_data, $error_pct ),
		);
	}

	/**
	 * Get indexing data from available sources.
	 *
	 * @since  1.6028.1445
	 * @return array Indexing data.
	 */
	private static function get_indexing_data() {
		// Try Google Site Kit.
		$sitekit_data = self::get_sitekit_indexing_data();
		if ( ! empty( $sitekit_data ) ) {
			return $sitekit_data;
		}

		// Try RankMath.
		$rankmath_data = self::get_rankmath_indexing_data();
		if ( ! empty( $rankmath_data ) ) {
			return $rankmath_data;
		}

		// Try Yoast SEO.
		$yoast_data = self::get_yoast_indexing_data();
		if ( ! empty( $yoast_data ) ) {
			return $yoast_data;
		}

		// Try custom WPShadow settings.
		$custom_data = self::get_custom_indexing_data();
		if ( ! empty( $custom_data ) ) {
			return $custom_data;
		}

		return array();
	}

	/**
	 * Get indexing data from Google Site Kit.
	 *
	 * @since  1.6028.1445
	 * @return array Indexing data.
	 */
	private static function get_sitekit_indexing_data() {
		if ( ! class_exists( 'Google\Site_Kit\Plugin' ) ) {
			return array();
		}

		$index_data = get_option( 'googlesitekit_search_console_index', array() );
		if ( empty( $index_data ) ) {
			return array();
		}

		return array(
			'total_pages'    => $index_data['total_pages'] ?? 0,
			'indexed_pages'  => $index_data['indexed_pages'] ?? 0,
			'error_pages'    => $index_data['error_pages'] ?? 0,
			'excluded_pages' => $index_data['excluded_pages'] ?? 0,
			'error_types'    => $index_data['error_types'] ?? array(),
			'source'         => 'google_site_kit',
		);
	}

	/**
	 * Get indexing data from RankMath.
	 *
	 * @since  1.6028.1445
	 * @return array Indexing data.
	 */
	private static function get_rankmath_indexing_data() {
		if ( ! class_exists( 'RankMath' ) ) {
			return array();
		}

		$index_data = get_option( 'rank_math_gsc_index', array() );
		if ( empty( $index_data ) ) {
			return array();
		}

		return array(
			'total_pages'    => $index_data['total'] ?? 0,
			'indexed_pages'  => $index_data['indexed'] ?? 0,
			'error_pages'    => $index_data['errors'] ?? 0,
			'excluded_pages' => $index_data['excluded'] ?? 0,
			'error_types'    => $index_data['error_types'] ?? array(),
			'source'         => 'rank_math',
		);
	}

	/**
	 * Get indexing data from Yoast SEO.
	 *
	 * @since  1.6028.1445
	 * @return array Indexing data.
	 */
	private static function get_yoast_indexing_data() {
		if ( ! class_exists( 'WPSEO_Options' ) ) {
			return array();
		}

		$index_data = get_option( 'wpseo_gsc_index', array() );
		if ( empty( $index_data ) ) {
			return array();
		}

		return array(
			'total_pages'    => $index_data['total'] ?? 0,
			'indexed_pages'  => $index_data['indexed'] ?? 0,
			'error_pages'    => $index_data['errors'] ?? 0,
			'excluded_pages' => $index_data['excluded'] ?? 0,
			'error_types'    => $index_data['error_types'] ?? array(),
			'source'         => 'yoast_seo',
		);
	}

	/**
	 * Get custom indexing data from WPShadow settings.
	 *
	 * @since  1.6028.1445
	 * @return array Indexing data.
	 */
	private static function get_custom_indexing_data() {
		$total  = get_option( 'wpshadow_gsc_total_pages', 0 );
		$errors = get_option( 'wpshadow_gsc_error_pages', 0 );

		if ( $total <= 0 ) {
			return array();
		}

		return array(
			'total_pages'    => $total,
			'indexed_pages'  => get_option( 'wpshadow_gsc_indexed_pages', 0 ),
			'error_pages'    => $errors,
			'excluded_pages' => get_option( 'wpshadow_gsc_excluded_pages', 0 ),
			'error_types'    => get_option( 'wpshadow_gsc_error_types', array() ),
			'source'         => 'wpshadow_custom',
		);
	}

	/**
	 * Check basic indexability when Search Console data unavailable.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding if indexability issues detected.
	 */
	private static function check_basic_indexability() {
		$issues = array();

		// Check robots.txt.
		if ( self::robots_blocks_indexing() ) {
			$issues[] = __( 'robots.txt may be blocking search engines', 'wpshadow' );
		}

		// Check meta robots.
		if ( self::has_noindex_meta() ) {
			$issues[] = __( 'Meta robots noindex detected on important pages', 'wpshadow' );
		}

		// Check sitemap.
		if ( ! self::has_sitemap() ) {
			$issues[] = __( 'XML sitemap not detected', 'wpshadow' );
		}

		// Check for broken links.
		if ( self::has_broken_internal_links() ) {
			$issues[] = __( 'Broken internal links detected', 'wpshadow' );
		}

		// If multiple issues found, flag it.
		if ( count( $issues ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => __( 'Indexability Issues Detected (GSC Data Unavailable)', 'wpshadow' ),
				'description'  => __( 'Multiple issues that may prevent proper indexing', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-console-indexing-errors',
				'meta'         => array(
					'issues_found' => $issues,
					'check_type'   => 'proxy_indicators',
				),
				'details'      => $issues,
				'recommendations' => array(
					__( 'Install Google Site Kit to track indexing status', 'wpshadow' ),
					__( 'Check robots.txt allows search engines', 'wpshadow' ),
					__( 'Remove noindex from important pages', 'wpshadow' ),
					__( 'Generate and submit XML sitemap', 'wpshadow' ),
					__( 'Fix broken internal links', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if robots.txt blocks indexing.
	 *
	 * @since  1.6028.1445
	 * @return bool True if blocking detected.
	 */
	private static function robots_blocks_indexing() {
		$robots_url = home_url( '/robots.txt' );
		$response   = wp_remote_get( $robots_url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$robots_content = wp_remote_retrieve_body( $response );

		// Check for aggressive disallow rules.
		return false !== stripos( $robots_content, 'Disallow: /' );
	}

	/**
	 * Check if homepage has noindex meta tag.
	 *
	 * @since  1.6028.1445
	 * @return bool True if noindex detected.
	 */
	private static function has_noindex_meta() {
		$response = wp_remote_get( home_url( '/' ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		return false !== stripos( $html, 'noindex' );
	}

	/**
	 * Check if XML sitemap exists.
	 *
	 * @since  1.6028.1445
	 * @return bool True if sitemap detected.
	 */
	private static function has_sitemap() {
		$sitemap_urls = array(
			home_url( '/sitemap.xml' ),
			home_url( '/sitemap_index.xml' ),
			home_url( '/wp-sitemap.xml' ),
		);

		foreach ( $sitemap_urls as $url ) {
			$response = wp_remote_head( $url );
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for broken internal links on homepage.
	 *
	 * @since  1.6028.1445
	 * @return bool True if broken links detected.
	 */
	private static function has_broken_internal_links() {
		$response = wp_remote_get( home_url( '/' ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		// Extract internal links.
		preg_match_all( '/<a[^>]+href=["\'](https?:\/\/[^"\']+)["\'][^>]*>/i', $html, $matches );
		$links = $matches[1] ?? array();

		$site_url = home_url( '/' );
		$broken   = 0;

		foreach ( $links as $link ) {
			// Only check internal links.
			if ( 0 !== strpos( $link, $site_url ) ) {
				continue;
			}

			$check_response = wp_remote_head( $link );
			$code           = wp_remote_retrieve_response_code( $check_response );

			if ( $code >= 400 ) {
				++$broken;
				if ( $broken >= 3 ) {
					return true; // 3+ broken links is significant.
				}
			}
		}

		return false;
	}

	/**
	 * Get error details based on indexing data.
	 *
	 * @since  1.6028.1445
	 * @param  array $indexing_data Indexing data.
	 * @return array Error details.
	 */
	private static function get_error_details( $indexing_data ) {
		$details = array();

		if ( ! empty( $indexing_data['error_types'] ) ) {
			foreach ( $indexing_data['error_types'] as $error_type => $count ) {
				$details[] = sprintf(
					/* translators: 1: error type, 2: count */
					__( '%1$s: %2$d pages', 'wpshadow' ),
					ucfirst( str_replace( '_', ' ', $error_type ) ),
					$count
				);
			}
		}

		if ( empty( $details ) ) {
			$details[] = __( 'Indexing errors detected in Google Search Console', 'wpshadow' );
		}

		$details[] = __( 'Errors prevent pages from appearing in search results', 'wpshadow' );
		$details[] = __( 'Lost traffic opportunity from un-indexed pages', 'wpshadow' );

		return $details;
	}

	/**
	 * Calculate severity based on error percentage.
	 *
	 * @since  1.6028.1445
	 * @param  float $error_pct Error percentage.
	 * @return string Severity level.
	 */
	private static function calculate_severity( $error_pct ) {
		if ( $error_pct >= 10 ) {
			return 'high';
		} elseif ( $error_pct >= 5 ) {
			return 'medium';
		} else {
			return 'low';
		}
	}

	/**
	 * Calculate threat level based on error percentage.
	 *
	 * @since  1.6028.1445
	 * @param  float $error_pct Error percentage.
	 * @return int Threat level (0-100).
	 */
	private static function calculate_threat_level( $error_pct ) {
		if ( $error_pct >= 10 ) {
			return 70;
		} elseif ( $error_pct >= 5 ) {
			return 60;
		} else {
			return 45;
		}
	}

	/**
	 * Get recommendations based on indexing errors.
	 *
	 * @since  1.6028.1445
	 * @param  array $indexing_data Indexing data.
	 * @param  float $error_pct Error percentage.
	 * @return array Recommendations.
	 */
	private static function get_recommendations( $indexing_data, $error_pct ) {
		$recommendations = array(
			__( 'Review Google Search Console Coverage report', 'wpshadow' ),
			__( 'Fix server errors (5xx) and not found errors (404)', 'wpshadow' ),
			__( 'Remove or update redirect chains', 'wpshadow' ),
			__( 'Check robots.txt is not blocking important pages', 'wpshadow' ),
			__( 'Ensure XML sitemap includes all important pages', 'wpshadow' ),
		);

		if ( ! empty( $indexing_data['error_types']['server_error'] ) ) {
			$recommendations[] = __( 'Fix server errors (500/503) immediately', 'wpshadow' );
		}

		if ( ! empty( $indexing_data['error_types']['redirect_error'] ) ) {
			$recommendations[] = __( 'Simplify redirect chains', 'wpshadow' );
		}

		if ( ! empty( $indexing_data['error_types']['soft_404'] ) ) {
			$recommendations[] = __( 'Fix soft 404 errors (return proper 404 status)', 'wpshadow' );
		}

		if ( $error_pct >= 10 ) {
			$recommendations[] = __( 'URGENT: High indexing error rate - immediate attention needed', 'wpshadow' );
		}

		return $recommendations;
	}
}
