<?php
/**
 * Monitoring Alert Fatigue Not Managed Diagnostic
 *
 * Checks if alert fatigue is managed.
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
 * Monitoring Alert Fatigue Not Managed Diagnostic Class
 *
 * Detects unmanaged alert fatigue.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Monitoring_Alert_Fatigue_Not_Managed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'monitoring-alert-fatigue-not-managed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Monitoring Alert Fatigue Not Managed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if alert fatigue is managed';

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
		// Check for alert threshold configuration
		if ( ! get_option( 'alert_threshold_configured' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Monitoring alert fatigue is not managed. Set intelligent thresholds, group related alerts, and use alert deduplication to prevent alert overload that causes critical issues to be missed.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/monitoring-alert-fatigue-not-managed',
			);
		}

		return null;
	}
}
