<?php
/**
 * Analytics Conversion Tracking Not Set Up Diagnostic
 *
 * Checks if conversion tracking is configured.
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
 * Analytics Conversion Tracking Not Set Up Diagnostic Class
 *
 * Detects missing conversion tracking.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Analytics_Conversion_Tracking_Not_Set_Up extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'analytics-conversion-tracking-not-set-up';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Analytics Conversion Tracking Not Set Up';

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
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for Google Analytics or conversion tracking
		if ( ! is_plugin_active( 'google-analytics-dashboard-for-wp/google-analytics-dashboard-for-wp.php' ) && ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Analytics conversion tracking is not set up. Configure conversion tracking to measure goal completions and user behavior.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/analytics-conversion-tracking-not-set-up',
			);
		}

		return null;
	}
}
