<?php
/**
 * User Behavior Analysis Diagnostic
 *
 * Tests if user engagement metrics are being tracked.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
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
 * Verifies that analytics or behavior tracking is active.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Analyzes_User_Behavior extends Diagnostic_Base {

	protected static $slug = 'analyzes-user-behavior';
	protected static $title = 'User Behavior Analysis';
	protected static $description = 'Tests if user engagement metrics are being tracked';
	protected static $family = 'publisher';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( self::has_analytics_plugin() ) {
			return null;
		}

		$manual_flag = get_option( 'wpshadow_user_behavior_tracking' );
		if ( $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No user behavior tracking detected. Use analytics or heatmaps to understand engagement.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/user-behavior-analysis',
			'persona'      => 'publisher',
		);
	}

	/**
	 * Check for analytics or heatmap plugins.
	 *
	 * @since  1.6050.0000
	 * @return bool True if detected.
	 */
	private static function has_analytics_plugin() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php',
			'ga-google-analytics/ga-google-analytics.php',
			'matomo/matomo.php',
			'hotjar/hotjar.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
