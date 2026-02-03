<?php
/**
 * Frontend Performance Monitoring Not Implemented Diagnostic
 *
 * Checks frontend monitoring.
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
 * Diagnostic_Frontend_Performance_Monitoring_Not_Implemented Class
 *
 * Performs diagnostic check for Frontend Performance Monitoring Not Implemented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Frontend_Performance_Monitoring_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'frontend-performance-monitoring-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Frontend Performance Monitoring Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks frontend monitoring';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'monitor_frontend_performance' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Frontend performance monitoring not implemented. Track Core Web Vitals,
						'severity'   =>   'medium',
						'threat_level'   =>   45,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/frontend-performance-monitoring-not-implemented'
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
