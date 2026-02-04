<?php
/**
 * No Content Performance Tracking Diagnostic
 *
 * Detects lack of content analytics tracking, preventing data-driven
 * optimization decisions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since      1.6034.2207
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Content Performance Tracking Diagnostic Class
 *
 * Checks if content performance metrics are being tracked to enable
 * data-driven content strategy decisions.
 *
 * **Why This Matters:**
 * - Can't improve what you don't measure
 * - Wasting effort on underperforming content
 * - Missing opportunities to scale winners
 * - No data for content ROI calculations
 * - Flying blind on content strategy
 *
 * **Key Metrics to Track:**
 * - Pageviews per post
 * - Time on page
 * - Bounce rate
 * - Social shares
 * - Conversions/goals
 * - Comments and engagement
 *
 * @since 1.6034.2207
 */
class Diagnostic_No_Content_Performance_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-performance-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Content Performance Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Content performance isn\'t being tracked, preventing data-driven optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2207
	 * @return array|null Finding array if tracking not configured, null otherwise.
	 */
	public static function check() {
		// Check if analytics is installed
		if ( ! self::has_analytics_plugin() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No analytics tracking detected. Install Google Analytics or similar to track content performance.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/analytics-content-tracking',
				'details'      => array(
					'message'          => 'Install analytics to make data-driven content decisions',
					'recommended_tool' => 'Site Kit by Google (includes Analytics)',
				),
			);
		}

		// Check if post-level tracking is configured
		if ( ! self::has_post_level_tracking() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Analytics is installed but post-level tracking isn\'t configured. Enable content grouping or custom dimensions.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/analytics-content-tracking',
				'details'      => array(
					'message' => 'Enable post-level tracking for better insights',
				),
			);
		}

		return null; // Tracking is configured
	}

	/**
	 * Check if analytics plugin is installed
	 *
	 * @since  1.6034.2207
	 * @return bool True if analytics plugin active, false otherwise.
	 */
	private static function has_analytics_plugin() {
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

		// Check for GA tracking code in header
		ob_start();
		wp_head();
		$head = ob_get_clean();

		return ( strpos( $head, 'gtag(' ) !== false || strpos( $head, 'ga(' ) !== false );
	}

	/**
	 * Check if post-level tracking is configured
	 *
	 * @since  1.6034.2207
	 * @return bool True if post tracking configured, false otherwise.
	 */
	private static function has_post_level_tracking() {
		// Check if popular analytics plugins have content tracking enabled
		if ( is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ) {
			// MonsterInsights tracks posts by default
			return true;
		}

		if ( is_plugin_active( 'google-analytics-dashboard-for-wp/gadwp.php' ) ) {
			// ExactMetrics tracks posts by default
			return true;
		}

		// Assume tracking if plugin is active
		return false;
	}
}
