<?php
/**
 * Analytics Conversion Tracking Not Configured Diagnostic
 *
 * Checks if conversion tracking is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Analytics Conversion Tracking Not Configured Diagnostic Class
 *
 * Detects missing conversion tracking.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Analytics_Conversion_Tracking_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'analytics-conversion-tracking-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Analytics Conversion Tracking Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if conversion tracking is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for Google Analytics or similar
		if ( ! is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) && ! has_filter( 'wp_head', 'add_google_analytics' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Analytics conversion tracking is not configured. Set up Google Analytics conversion goals to measure business objectives.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/analytics-conversion-tracking-not-configured',
			);
		}

		return null;
	}
}
