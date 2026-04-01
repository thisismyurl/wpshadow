<?php
/**
 * Zero-Day Monitoring Not Configured Diagnostic
 *
 * Checks zero-day monitoring.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Zero_Day_Monitoring_Not_Configured Class
 *
 * Performs diagnostic check for Zero Day Monitoring Not Configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Zero_Day_Monitoring_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'zero-day-monitoring-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Zero-Day Monitoring Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks zero-day monitoring';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'zeroday_monitoring_enabled' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Zero-day monitoring is not configured yet. Tracking trusted WordPress security advisories helps you respond faster to new vulnerabilities.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/zero-day-monitoring-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
