<?php
/**
 * Error Rate Monitoring Not Implemented Diagnostic
 *
 * Checks error monitoring.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Error_Rate_Monitoring_Not_Implemented Class
 *
 * Performs diagnostic check for Error Rate Monitoring Not Implemented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Error_Rate_Monitoring_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-rate-monitoring-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Error Rate Monitoring Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks error monitoring';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'monitor_error_rates' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Error rate monitoring not implemented. Track error logs and alert when error rates exceed thresholds.',
						'severity'   =>   'medium',
						'threat_level'   =>   40,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/error-rate-monitoring-not-implemented'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
