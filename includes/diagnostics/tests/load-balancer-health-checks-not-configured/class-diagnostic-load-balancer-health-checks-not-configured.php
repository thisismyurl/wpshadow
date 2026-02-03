<?php
/**
 * Load Balancer Health Checks Not Configured Diagnostic
 *
 * Checks health checks.
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
 * Diagnostic_Load_Balancer_Health_Checks_Not_Configured Class
 *
 * Performs diagnostic check for Load Balancer Health Checks Not Configured.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Load_Balancer_Health_Checks_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'load-balancer-health-checks-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Load Balancer Health Checks Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks health checks';

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
						'configure_health_checks' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Load balancer health checks not configured. Implement endpoints that verify backend availability without side effects.',
						'severity'   =>   'medium',
						'threat_level'   =>   45,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/load-balancer-health-checks-not-configured'
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
