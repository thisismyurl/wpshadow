<?php
/**
 * Load Balancer Health Checks Not Configured Diagnostic
 *
 * Checks health checks.
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
 * Diagnostic_Load_Balancer_Health_Checks_Not_Configured Class
 *
 * Performs diagnostic check for Load Balancer Health Checks Not Configured.
 *
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'configure_health_checks' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Load balancer health checks are not configured yet. Adding reliable health endpoints helps traffic route only to healthy servers.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/load-balancer-health-checks-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
