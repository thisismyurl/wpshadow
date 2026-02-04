<?php
/**
 * User Behavior Analysis Diagnostic
 *
 * Tests if user engagement metrics are tracked including heatmaps,
 * scroll depth, click tracking, and session recordings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since      1.6035.1635
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Behavior Analysis Diagnostic Class
 *
 * Verifies user behavior tracking tools are configured to enable
 * data-driven UX optimization and content strategy.
 *
 * @since 1.6035.1635
 */
class Diagnostic_User_Behavior extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'analyzes_user_behavior';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'User Behavior Analysis';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies user engagement metrics and behavior are tracked';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1635
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for heatmap/click tracking (30 points).
		$heatmap_plugins = array(
			'hotjar/hotjar.php'                                 => 'Hotjar',
			'crazy-egg/crazy-egg.php'                           => 'Crazy Egg',
			'mouseflow/mouseflow.php'                           => 'Mouseflow',
			'clicktale/clicktale.php'                           => 'ClickTale',
		);

		$active_heatmap = array();
		foreach ( $heatmap_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_heatmap[] = $plugin_name;
				$earned_points   += 15; // Up to 30 points.
			}
		}

		if ( count( $active_heatmap ) > 0 ) {
			$stats['heatmap_plugins'] = implode( ', ', $active_heatmap );
		} else {
			$issues[] = 'No heatmap or click tracking detected';
		}

		// Check for scroll depth tracking (25 points).
		$scroll_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights (scroll depth)',
			'scroll-depth/scroll-depth.php'                       => 'Scroll Depth',
			'ga-google-analytics/ga-google-analytics.php'         => 'GA Google Analytics',
		);

		$active_scroll = array();
		foreach ( $scroll_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_scroll[] = $plugin_name;
				$earned_points  += 8; // Up to 25 points.
			}
		}

		if ( count( $active_scroll ) > 0 ) {
			$stats['scroll_tracking_plugins'] = implode( ', ', $active_scroll );
		} else {
			$warnings[] = 'No scroll depth tracking detected';
		}

		// Check for event tracking (20 points).
		$event_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-tag-manager-for-wordpress/google-tag-manager-for-wordpress.php' => 'GTM4WP',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
		);

		$active_event = array();
		foreach ( $event_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_event[] = $plugin_name;
				$earned_points += 7; // Up to 20 points.
			}
		}

		if ( count( $active_event ) > 0 ) {
			$stats['event_tracking_plugins'] = implode( ', ', $active_event );
		} else {
			$warnings[] = 'No event tracking detected';
		}

		// Check for session recording (15 points).
		$session_plugins = array(
			'hotjar/hotjar.php'                                 => 'Hotjar',
			'mouseflow/mouseflow.php'                           => 'Mouseflow',
			'fullstory/fullstory.php'                           => 'FullStory',
		);

		$active_session = array();
		foreach ( $session_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_session[] = $plugin_name;
				$earned_points   += 8; // Up to 15 points.
			}
		}

		if ( count( $active_session ) > 0 ) {
			$stats['session_recording_plugins'] = implode( ', ', $active_session );
		}

		// Check for time on page tracking (10 points).
		$time_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
		);

		$active_time = array();
		foreach ( $time_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_time[] = $plugin_name;
				$earned_points += 5; // Up to 10 points.
			}
		}

		if ( count( $active_time ) > 0 ) {
			$stats['time_tracking_plugins'] = implode( ', ', $active_time );
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
					__( 'Your user behavior tracking scored %s. Understanding how users interact with your site (what they click, how far they scroll, where they get stuck) is essential for UX optimization. Heatmaps and session recordings reveal issues analytics alone can\'t show.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-behavior-analysis',
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
