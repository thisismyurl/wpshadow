<?php
/**
 * Web Performance Monitoring Not Configured Diagnostic
 *
 * Checks performance monitoring.
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
 * Diagnostic_Web_Performance_Monitoring_Not_Configured Class
 *
 * Performs diagnostic check for Web Performance Monitoring Not Configured.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Web_Performance_Monitoring_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'web-performance-monitoring-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Web Performance Monitoring Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks performance monitoring';

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
		if (   !get_option('performance_monitoring_service' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Web performance monitoring not configured. Set up monitoring with DataDog,
						'severity'   =>   'medium',
						'threat_level'   =>   35,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/web-performance-monitoring-not-configured'
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
