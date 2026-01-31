<?php
/**
 * Frontend Performance Metrics Not Monitored Diagnostic
 *
 * Checks if performance metrics are monitored.
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
 * Frontend Performance Metrics Not Monitored Diagnostic Class
 *
 * Detects missing performance monitoring.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Frontend_Performance_Metrics_Not_Monitored extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'frontend-performance-metrics-not-monitored';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Frontend Performance Metrics Not Monitored';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance metrics are monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for performance monitoring
		if ( ! is_plugin_active( 'perfmatrix/perfmatrix.php' ) && ! is_plugin_active( 'google-analytics-dashboard-for-wp/google-analytics-dashboard-for-wp.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Frontend performance metrics are not monitored. Set up continuous monitoring of Core Web Vitals and page load times.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/frontend-performance-metrics-not-monitored',
			);
		}

		return null;
	}
}
