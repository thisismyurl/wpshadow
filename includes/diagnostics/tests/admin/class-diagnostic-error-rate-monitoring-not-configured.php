<?php
/**
 * Error Rate Monitoring Not Configured Diagnostic
 *
 * Checks if error rate monitoring is configured.
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
 * Error Rate Monitoring Not Configured Diagnostic Class
 *
 * Detects missing error rate monitoring.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Error_Rate_Monitoring_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-rate-monitoring-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Error Rate Monitoring Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if error rate monitoring is configured';

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
		// Check for error monitoring service
		if ( ! has_option( 'error_monitoring_enabled' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Error rate monitoring is not configured. Set up error tracking (Sentry, Rollbar, Bugsnag) to catch and resolve issues before users report them.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/error-rate-monitoring-not-configured',
			);
		}

		return null;
	}
}
