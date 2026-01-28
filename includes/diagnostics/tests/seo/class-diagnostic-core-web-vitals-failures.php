<?php
/**
 * Core Web Vitals Failures Diagnostic
 *
 * Identifies pages failing Core Web Vitals thresholds in Google Search Console,
 * affecting page experience rankings and user satisfaction.
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
 * Core Web Vitals Failures Diagnostic Class
 *
 * Checks for Core Web Vitals (LCP, FID, CLS) failures that impact
 * page experience and search rankings.
 *
 * @since 1.6028.1445
 */
class Diagnostic_Core_Web_Vitals_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'core-web-vitals-failures';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Core Web Vitals Failures in Search Console';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Identifies pages failing Core Web Vitals thresholds in Google Search Console';

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
		$cache_key = 'wpshadow_cwv_failures_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$result = self::check_core_web_vitals();

		// Cache for 24 hours.
		set_transient( $cache_key, $result, DAY_IN_SECONDS );

		return $result;
	}

	/**
	 * Check Core Web Vitals status.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	private static function check_core_web_vitals() {
		// Try to get CWV data from available sources.
		$cwv_data = self::get_cwv_data();

		if ( empty( $cwv_data ) ) {
			// No CWV data available - check for basic performance issues.
			return self::check_basic_performance();
		}

		$total_urls  = $cwv_data['total_urls'] ?? 0;
		$poor_urls   = $cwv_data['poor_urls'] ?? 0;
		$needs_urls  = $cwv_data['needs_improvement_urls'] ?? 0;
		$good_urls   = $cwv_data['good_urls'] ?? 0;

		if ( $total_urls <= 0 ) {
			return null;
		}

		// Calculate percentage passing (good only).
		$passing_pct = ( $good_urls / $total_urls ) * 100;

		// Determine if this is an issue.
		if ( $passing_pct >= 75 ) {
			return null; // 75%+ passing is acceptable.
		}

		$severity     = self::calculate_severity( $passing_pct );
		$threat_level = self::calculate_threat_level( $passing_pct );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: percentage passing, 2: number of poor URLs, 3: total URLs */
				__( 'Only %1$.1f%% of URLs passing Core Web Vitals (%2$d poor out of %3$d total)', 'wpshadow' ),
				$passing_pct,
				$poor_urls,
				$total_urls
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/core-web-vitals',
			'meta'         => array(
				'total_urls'              => $total_urls,
				'good_urls'               => $good_urls,
				'needs_improvement_urls'  => $needs_urls,
				'poor_urls'               => $poor_urls,
				'passing_percent'         => $passing_pct,
				'data_source'             => $cwv_data['source'] ?? 'unknown',
				'lcp_issues'              => $cwv_data['lcp_issues'] ?? 0,
				'fid_issues'              => $cwv_data['fid_issues'] ?? 0,
				'cls_issues'              => $cwv_data['cls_issues'] ?? 0,
			),
			'details'      => self::get_issue_details( $cwv_data ),
			'recommendations' => self::get_recommendations( $cwv_data, $passing_pct ),
		);
	}

	/**
	 * Get Core Web Vitals data from available sources.
	 *
	 * @since  1.6028.1445
	 * @return array CWV data.
	 */
	private static function get_cwv_data() {
		// Try Google Site Kit.
		$sitekit_data = self::get_sitekit_cwv_data();
		if ( ! empty( $sitekit_data ) ) {
			return $sitekit_data;
		}

		// Try RankMath.
		$rankmath_data = self::get_rankmath_cwv_data();
		if ( ! empty( $rankmath_data ) ) {
			return $rankmath_data;
		}

		// Try custom WPShadow settings.
		$custom_data = self::get_custom_cwv_data();
		if ( ! empty( $custom_data ) ) {
			return $custom_data;
		}

		return array();
	}

	/**
	 * Get CWV data from Google Site Kit.
	 *
	 * @since  1.6028.1445
	 * @return array CWV data.
	 */
	private static function get_sitekit_cwv_data() {
		if ( ! class_exists( 'Google\Site_Kit\Plugin' ) ) {
			return array();
		}

		$cwv_data = get_option( 'googlesitekit_pagespeed_cwv', array() );
		if ( empty( $cwv_data ) ) {
			return array();
		}

		return array(
			'total_urls'              => $cwv_data['total_urls'] ?? 0,
			'good_urls'               => $cwv_data['good_urls'] ?? 0,
			'needs_improvement_urls'  => $cwv_data['needs_improvement_urls'] ?? 0,
			'poor_urls'               => $cwv_data['poor_urls'] ?? 0,
			'lcp_issues'              => $cwv_data['lcp_issues'] ?? 0,
			'fid_issues'              => $cwv_data['fid_issues'] ?? 0,
			'cls_issues'              => $cwv_data['cls_issues'] ?? 0,
			'source'                  => 'google_site_kit',
		);
	}

	/**
	 * Get CWV data from RankMath.
	 *
	 * @since  1.6028.1445
	 * @return array CWV data.
	 */
	private static function get_rankmath_cwv_data() {
		if ( ! class_exists( 'RankMath' ) ) {
			return array();
		}

		$cwv_data = get_option( 'rank_math_cwv_data', array() );
		if ( empty( $cwv_data ) ) {
			return array();
		}

		return array(
			'total_urls'              => $cwv_data['total'] ?? 0,
			'good_urls'               => $cwv_data['good'] ?? 0,
			'needs_improvement_urls'  => $cwv_data['needs_improvement'] ?? 0,
			'poor_urls'               => $cwv_data['poor'] ?? 0,
			'lcp_issues'              => $cwv_data['lcp_issues'] ?? 0,
			'fid_issues'              => $cwv_data['fid_issues'] ?? 0,
			'cls_issues'              => $cwv_data['cls_issues'] ?? 0,
			'source'                  => 'rank_math',
		);
	}

	/**
	 * Get custom CWV data from WPShadow settings.
	 *
	 * @since  1.6028.1445
	 * @return array CWV data.
	 */
	private static function get_custom_cwv_data() {
		$total = get_option( 'wpshadow_cwv_total_urls', 0 );
		$good  = get_option( 'wpshadow_cwv_good_urls', 0 );
		$needs = get_option( 'wpshadow_cwv_needs_improvement_urls', 0 );
		$poor  = get_option( 'wpshadow_cwv_poor_urls', 0 );

		if ( $total <= 0 ) {
			return array();
		}

		return array(
			'total_urls'              => $total,
			'good_urls'               => $good,
			'needs_improvement_urls'  => $needs,
			'poor_urls'               => $poor,
			'lcp_issues'              => get_option( 'wpshadow_cwv_lcp_issues', 0 ),
			'fid_issues'              => get_option( 'wpshadow_cwv_fid_issues', 0 ),
			'cls_issues'              => get_option( 'wpshadow_cwv_cls_issues', 0 ),
			'source'                  => 'wpshadow_custom',
		);
	}

	/**
	 * Check basic performance as proxy when CWV data unavailable.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding if basic performance issues detected.
	 */
	private static function check_basic_performance() {
		$issues = array();

		// Test homepage load time.
		$load_time = self::measure_page_load_time( home_url( '/' ) );
		if ( $load_time > 3.0 ) {
			$issues[] = sprintf(
				/* translators: %s: load time in seconds */
				__( 'Homepage loads in %s seconds (should be < 3s)', 'wpshadow' ),
				number_format( $load_time, 2 )
			);
		}

		// Check for optimization plugins.
		if ( ! self::has_performance_plugin() ) {
			$issues[] = __( 'No performance optimization plugin detected', 'wpshadow' );
		}

		// Check for large images.
		if ( self::has_large_images() ) {
			$issues[] = __( 'Large unoptimized images detected', 'wpshadow' );
		}

		// If multiple issues found, flag it.
		if ( count( $issues ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => __( 'Performance Issues Detected (CWV Data Unavailable)', 'wpshadow' ),
				'description'  => __( 'Multiple performance issues that may affect Core Web Vitals', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/core-web-vitals',
				'meta'         => array(
					'issues_found' => $issues,
					'check_type'   => 'proxy_indicators',
				),
				'details'      => $issues,
				'recommendations' => array(
					__( 'Install Google Site Kit to track Core Web Vitals', 'wpshadow' ),
					__( 'Use performance optimization plugin (WP Rocket, etc.)', 'wpshadow' ),
					__( 'Optimize images and use lazy loading', 'wpshadow' ),
					__( 'Enable caching and minification', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Measure page load time.
	 *
	 * @since  1.6028.1445
	 * @param  string $url URL to test.
	 * @return float Load time in seconds.
	 */
	private static function measure_page_load_time( $url ) {
		$start    = microtime( true );
		$response = wp_remote_get( $url );
		$end      = microtime( true );

		if ( is_wp_error( $response ) ) {
			return 0.0;
		}

		return $end - $start;
	}

	/**
	 * Check if performance optimization plugin is active.
	 *
	 * @since  1.6028.1445
	 * @return bool True if performance plugin detected.
	 */
	private static function has_performance_plugin() {
		$plugins = array(
			'WP_Rocket',
			'WP_Optimize',
			'W3TC',
			'Autoptimize',
			'LiteSpeed_Cache',
			'WP_Super_Cache',
		);

		foreach ( $plugins as $plugin ) {
			if ( class_exists( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for large unoptimized images on homepage.
	 *
	 * @since  1.6028.1445
	 * @return bool True if large images detected.
	 */
	private static function has_large_images() {
		$response = wp_remote_get( home_url( '/' ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for images without srcset or loading attributes.
		preg_match_all( '/<img[^>]+>/i', $html, $matches );
		$total_images = count( $matches[0] ?? array() );

		if ( $total_images === 0 ) {
			return false;
		}

		$unoptimized = 0;
		foreach ( $matches[0] as $img ) {
			if ( false === strpos( $img, 'srcset' ) && false === strpos( $img, 'loading=' ) ) {
				++$unoptimized;
			}
		}

		// If >50% of images are unoptimized, flag it.
		return $unoptimized > ( $total_images / 2 );
	}

	/**
	 * Get issue details based on CWV data.
	 *
	 * @since  1.6028.1445
	 * @param  array $cwv_data CWV data.
	 * @return array Issue details.
	 */
	private static function get_issue_details( $cwv_data ) {
		$details = array();

		if ( ! empty( $cwv_data['lcp_issues'] ) ) {
			$details[] = sprintf(
				/* translators: %d: number of LCP issues */
				__( 'LCP (Largest Contentful Paint) issues: %d pages', 'wpshadow' ),
				$cwv_data['lcp_issues']
			);
		}

		if ( ! empty( $cwv_data['fid_issues'] ) ) {
			$details[] = sprintf(
				/* translators: %d: number of FID issues */
				__( 'FID (First Input Delay) issues: %d pages', 'wpshadow' ),
				$cwv_data['fid_issues']
			);
		}

		if ( ! empty( $cwv_data['cls_issues'] ) ) {
			$details[] = sprintf(
				/* translators: %d: number of CLS issues */
				__( 'CLS (Cumulative Layout Shift) issues: %d pages', 'wpshadow' ),
				$cwv_data['cls_issues']
			);
		}

		if ( empty( $details ) ) {
			$details[] = __( 'Core Web Vitals failures detected across multiple pages', 'wpshadow' );
		}

		$details[] = __( 'Poor Core Web Vitals affect page experience rankings', 'wpshadow' );
		$details[] = __( 'Users experience slower loading and interaction', 'wpshadow' );

		return $details;
	}

	/**
	 * Calculate severity based on passing percentage.
	 *
	 * @since  1.6028.1445
	 * @param  float $passing_pct Percentage passing.
	 * @return string Severity level.
	 */
	private static function calculate_severity( $passing_pct ) {
		if ( $passing_pct < 50 ) {
			return 'high';
		} elseif ( $passing_pct < 75 ) {
			return 'medium';
		} else {
			return 'low';
		}
	}

	/**
	 * Calculate threat level based on passing percentage.
	 *
	 * @since  1.6028.1445
	 * @param  float $passing_pct Percentage passing.
	 * @return int Threat level (0-100).
	 */
	private static function calculate_threat_level( $passing_pct ) {
		if ( $passing_pct < 50 ) {
			return 75;
		} elseif ( $passing_pct < 75 ) {
			return 60;
		} else {
			return 45;
		}
	}

	/**
	 * Get recommendations based on CWV issues.
	 *
	 * @since  1.6028.1445
	 * @param  array $cwv_data CWV data.
	 * @param  float $passing_pct Passing percentage.
	 * @return array Recommendations.
	 */
	private static function get_recommendations( $cwv_data, $passing_pct ) {
		$recommendations = array(
			__( 'Use Google PageSpeed Insights to identify specific issues', 'wpshadow' ),
			__( 'Enable caching and compression', 'wpshadow' ),
			__( 'Optimize images and use modern formats (WebP)', 'wpshadow' ),
			__( 'Minimize JavaScript and CSS', 'wpshadow' ),
			__( 'Use a CDN for faster content delivery', 'wpshadow' ),
		);

		if ( ! empty( $cwv_data['lcp_issues'] ) ) {
			$recommendations[] = __( 'LCP: Optimize largest contentful paint (images, videos, text blocks)', 'wpshadow' );
		}

		if ( ! empty( $cwv_data['fid_issues'] ) ) {
			$recommendations[] = __( 'FID: Reduce JavaScript execution time', 'wpshadow' );
		}

		if ( ! empty( $cwv_data['cls_issues'] ) ) {
			$recommendations[] = __( 'CLS: Fix layout shifts (reserve space for images/ads)', 'wpshadow' );
		}

		if ( $passing_pct < 50 ) {
			$recommendations[] = __( 'URGENT: Major performance optimization needed', 'wpshadow' );
		}

		return $recommendations;
	}
}
