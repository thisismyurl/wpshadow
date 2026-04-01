<?php
/**
 * High Bounce Rate Content Diagnostic
 *
 * Identifies posts with unusually high bounce rates that need optimization
 * to better engage visitors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * High Bounce Rate Content Diagnostic Class
 *
 * Analyzes content engagement metrics to detect posts where visitors
 * leave immediately, indicating content quality or relevance issues.
 *
 * **Why This Matters:**
 * - High bounce rates hurt SEO rankings
 * - Indicates content doesn't meet expectations
 * - Reduces conversion opportunities
 * - Average bounce rate: 40-60%
 * - Above 70% = significant problem
 *
 * **Common Causes:**
 * - Misleading titles
 * - Slow loading speed
 * - Poor content quality
 * - No clear value proposition
 * - Difficult to read
 *
 * @since 0.6093.1200
 */
class Diagnostic_High_Bounce_Rate_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'high-bounce-rate-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'High Bounce Rate Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects content with high bounce rates needing optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if high bounce rate detected, null otherwise.
	 */
	public static function check() {
		// Check if analytics plugin is active
		if ( ! self::has_analytics_integration() ) {
			return null;
		}

		// Get bounce rate data from transient cache
		$bounce_rate_data = get_transient( 'wpshadow_bounce_rate_data' );

		if ( false === $bounce_rate_data ) {
			// No cached data - return guidance to enable tracking
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to analyze bounce rates. Enable Google Analytics or similar tracking to monitor content performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/analytics-bounce-rate?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'message' => 'Install analytics tracking to detect high bounce rates',
				),
			);
		}

		// Analyze posts with high bounce rates
		$high_bounce_posts = array();
		foreach ( $bounce_rate_data as $post_data ) {
			if ( isset( $post_data['bounce_rate'] ) && $post_data['bounce_rate'] > 70 ) {
				$high_bounce_posts[] = $post_data;
			}
		}

		if ( empty( $high_bounce_posts ) ) {
			return null;
		}

		$count = count( $high_bounce_posts );
		$avg_bounce_rate = array_sum( array_column( $high_bounce_posts, 'bounce_rate' ) ) / $count;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of posts, 2: average bounce rate */
				__( '%1$d post(s) have high bounce rates (avg: %2$d%%). Review and improve content engagement.', 'wpshadow' ),
				$count,
				round( $avg_bounce_rate )
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/analytics-bounce-rate?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'high_bounce_posts'  => $count,
				'average_bounce_rate' => round( $avg_bounce_rate, 1 ),
				'sample_posts'       => array_slice( $high_bounce_posts, 0, 10 ),
			),
		);
	}

	/**
	 * Check if analytics integration is available
	 *
	 * @since 0.6093.1200
	 * @return bool True if analytics available, false otherwise.
	 */
	private static function has_analytics_integration() {
		// Check for popular analytics plugins
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php', // MonsterInsights
			'google-analytics-dashboard-for-wp/gadwp.php',        // ExactMetrics
			'google-site-kit/google-site-kit.php',                // Site Kit
		);

		foreach ( $analytics_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
