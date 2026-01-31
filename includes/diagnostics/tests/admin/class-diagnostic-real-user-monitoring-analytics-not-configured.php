<?php
/**
 * Real User Monitoring Analytics Not Configured Diagnostic
 *
 * Checks if real user monitoring is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Real User Monitoring Analytics Not Configured Diagnostic Class
 *
 * Detects unconfigured real user monitoring.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Real_User_Monitoring_Analytics_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'real-user-monitoring-analytics-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Real User Monitoring Analytics Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if real user monitoring is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for real user monitoring
		if ( ! is_plugin_active( 'google-analytics-dashboard-for-wp/google-analytics-dashboard-for-wp.php' ) && ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Real user monitoring analytics is not configured. Set up RUM to measure actual user experience and identify performance bottlenecks.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/real-user-monitoring-analytics-not-configured',
			);
		}

		return null;
	}
}
