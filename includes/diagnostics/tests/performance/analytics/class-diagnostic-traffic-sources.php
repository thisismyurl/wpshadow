<?php
/**
 * Traffic Source Understanding Diagnostic
 *
 * Tests if site owner understands and analyzes traffic sources through
 * analytics tools and tracking implementations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Traffic Source Understanding Diagnostic Class
 *
 * Verifies proper traffic source tracking and analytics configuration
 * to enable data-driven marketing decisions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Traffic_Sources extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'understands_traffic_sources';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Traffic Source Understanding';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies traffic sources are tracked and analyzed';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for Google Analytics (30 points).
		$ga_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics',
			'ga-google-analytics/ga-google-analytics.php'        => 'GA Google Analytics',
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
		);

		$active_ga = array();
		foreach ( $ga_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_ga[]    = $plugin_name;
				$earned_points += 15; // Up to 30 points.
			}
		}

		if ( count( $active_ga ) > 0 ) {
			$stats['google_analytics_plugins'] = implode( ', ', $active_ga );
		} else {
			$issues[] = 'No Google Analytics plugins detected';
		}

		// Check for UTM parameter tracking (25 points).
		$utm_plugins = array(
			'ga-google-analytics/ga-google-analytics.php'        => 'GA Google Analytics',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'pretty-links/pretty-links.php'                      => 'Pretty Links',
			'betterlinks/betterlinks.php'                        => 'BetterLinks',
		);

		$active_utm = array();
		foreach ( $utm_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_utm[]   = $plugin_name;
				$earned_points += 8; // Up to 25 points.
			}
		}

		if ( count( $active_utm ) > 0 ) {
			$stats['utm_tracking_plugins'] = implode( ', ', $active_utm );
		} else {
			$warnings[] = 'No UTM parameter tracking detected';
		}

		// Check for social media tracking (20 points).
		$social_plugins = array(
			'facebook-for-wordpress/facebook-for-wordpress.php' => 'Facebook for WordPress',
			'official-facebook-pixel/official-facebook-pixel.php' => 'Facebook Pixel',
			'twitter/twitter.php'                                 => 'Twitter',
			'instagram-feed/instagram-feed.php'                   => 'Instagram Feed',
		);

		$active_social = array();
		foreach ( $social_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_social[] = $plugin_name;
				$earned_points  += 7; // Up to 20 points.
			}
		}

		if ( count( $active_social ) > 0 ) {
			$stats['social_tracking_plugins'] = implode( ', ', $active_social );
		} else {
			$warnings[] = 'No social media tracking detected';
		}

		// Check for referrer tracking (15 points).
		if ( function_exists( 'wp_get_referer' ) ) {
			$earned_points += 15;
			$stats['referrer_tracking'] = 'WordPress native support';
		}

		// Check for analytics dashboard plugins (10 points).
		$dashboard_plugins = array(
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics Dashboard',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
		);

		$active_dashboard = array();
		foreach ( $dashboard_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_dashboard[] = $plugin_name;
				$earned_points     += 5; // Up to 10 points.
			}
		}

		if ( count( $active_dashboard ) > 0 ) {
			$stats['dashboard_plugins'] = implode( ', ', $active_dashboard );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 50%.
		if ( $score < 50 ) {
			$severity     = $score < 30 ? 'medium' : 'low';
			$threat_level = $score < 30 ? 40 : 30;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your traffic source tracking scored %s. Understanding where your visitors come from (search engines, social media, referrals, direct traffic) is essential for effective marketing. Without analytics, you\'re making decisions blind.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/traffic-source-understanding',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
