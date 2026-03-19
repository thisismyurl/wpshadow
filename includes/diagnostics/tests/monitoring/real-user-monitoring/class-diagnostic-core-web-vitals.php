<?php
/**
 * Core Web Vitals Monitoring Diagnostic
 *
 * Checks if Core Web Vitals (LCP, FID, CLS) are being monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core Web Vitals Monitoring Diagnostic Class
 *
 * Verifies Core Web Vitals are monitored for real user experience.
 * Like tracking how fast customers can shop in your store.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Core_Web_Vitals extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'core-web-vitals';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core Web Vitals Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Core Web Vitals (LCP, FID, CLS) are being monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'real-user-monitoring';

	/**
	 * Run the Core Web Vitals monitoring diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if monitoring issues detected, null otherwise.
	 */
	public static function check() {
		// Check for Google Analytics 4 (has Web Vitals).
		$has_ga4 = false;
		$head_content = self::get_head_content_sample();
		
		if ( false !== strpos( $head_content, 'gtag' ) || false !== strpos( $head_content, 'analytics.js' ) || false !== strpos( $head_content, 'gtm.js' ) ) {
			$has_ga4 = true;
		}

		// Check for Web Vitals reporting plugins.
		$monitoring_tools = array(
			'Site Kit by Google'  => defined( 'GOOGLESITEKIT_VERSION' ),
			'Query Monitor'       => class_exists( 'QueryMonitor' ),
			'New Relic'           => function_exists( 'newrelic_get_browser_timing_header' ),
			'Perfmatters'         => defined( 'PERFMATTERS_VERSION' ),
		);

		$active_tools = array();
		foreach ( $monitoring_tools as $name => $detected ) {
			if ( $detected ) {
				$active_tools[] = $name;
			}
		}

		// Check for Performance API usage (web-vitals.js library).
		$performance_api = get_option( 'wpshadow_web_vitals_configured', false );
		if ( $performance_api ) {
			$active_tools[] = 'Web Vitals API';
		}

		if ( empty( $active_tools ) && ! $has_ga4 ) {
			return array(
				'id'           => self::$slug . '-not-monitored',
				'title'        => __( 'Core Web Vitals Not Being Monitored', 'wpshadow' ),
				'description'  => __( 'You\'re not tracking Core Web Vitals (Google\'s speed metrics that affect search rankings). Core Web Vitals measure real user experience: how fast your largest content loads (LCP), how quickly visitors can interact (FID), and how stable your page layout is (CLS). Without monitoring, you don\'t know if visitors are having a good experience—and Google penalizes slow sites in search results. Set up Google Analytics 4 or use Google Site Kit (free plugin) to track these metrics.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/core-web-vitals',
				'context'      => array(),
			);
		}

		// Check actual Web Vitals scores if available.
		$lcp = get_transient( 'wpshadow_lcp_score' );
		$fid = get_transient( 'wpshadow_fid_score' );
		$cls = get_transient( 'wpshadow_cls_score' );

		if ( false !== $lcp || false !== $fid || false !== $cls ) {
			$poor_metrics = array();

			// LCP should be < 2.5s (good), 2.5-4s (needs improvement), >4s (poor).
			if ( false !== $lcp && $lcp > 4000 ) {
				$poor_metrics['LCP'] = sprintf(
					/* translators: %s: LCP time */
					__( 'Largest Contentful Paint: %s (should be under 2.5s)', 'wpshadow' ),
					number_format( $lcp / 1000, 2 ) . 's'
				);
			}

			// FID should be < 100ms (good), 100-300ms (needs improvement), >300ms (poor).
			if ( false !== $fid && $fid > 300 ) {
				$poor_metrics['FID'] = sprintf(
					/* translators: %s: FID time */
					__( 'First Input Delay: %s (should be under 100ms)', 'wpshadow' ),
					number_format( $fid, 0 ) . 'ms'
				);
			}

			// CLS should be < 0.1 (good), 0.1-0.25 (needs improvement), >0.25 (poor).
			if ( false !== $cls && $cls > 0.25 ) {
				$poor_metrics['CLS'] = sprintf(
					/* translators: %s: CLS score */
					__( 'Cumulative Layout Shift: %s (should be under 0.1)', 'wpshadow' ),
					number_format( $cls, 3 )
				);
			}

			if ( ! empty( $poor_metrics ) ) {
				return array(
					'id'           => self::$slug . '-poor-scores',
					'title'        => __( 'Poor Core Web Vitals Scores', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %s: list of poor metrics */
						__( 'Your Core Web Vitals scores are below Google\'s thresholds (like getting a low grade on speed tests). Poor metrics: %s. This hurts search rankings and user experience. Common fixes: optimize images, reduce JavaScript, eliminate render-blocking resources, fix layout shifts. Use Google PageSpeed Insights for specific recommendations.', 'wpshadow' ),
						implode( '; ', $poor_metrics )
					),
					'severity'     => 'high',
					'threat_level' => 75,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/improve-web-vitals',
					'context'      => array(
						'lcp' => $lcp,
						'fid' => $fid,
						'cls' => $cls,
						'poor_metrics' => $poor_metrics,
					),
				);
			}
		}

		return null; // Web Vitals are being monitored and scores are acceptable.
	}

	/**
	 * Get sample of head content for script detection.
	 *
	 * @since 1.6093.1200
	 * @return string Head content sample.
	 */
	private static function get_head_content_sample() {
		// Get cached sample if available.
		$cached = get_transient( 'wpshadow_head_content_sample' );
		if ( false !== $cached ) {
			return $cached;
		}

		// Capture head output.
		ob_start();
		wp_head();
		$head_content = ob_get_clean();

		// Cache for 1 day.
		set_transient( 'wpshadow_head_content_sample', $head_content, DAY_IN_SECONDS );

		return $head_content;
	}
}
