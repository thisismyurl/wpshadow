<?php
/**
 * Web Performance Monitoring Not Configured Diagnostic
 *
 * Checks performance monitoring.
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
 * Diagnostic_Web_Performance_Monitoring_Not_Configured Class
 *
 * Performs diagnostic check for Web Performance Monitoring Not Configured.
 *
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$monitoring_service = (string) get_option( 'performance_monitoring_service', '' );

		if ( '' === $monitoring_service ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Web performance monitoring is not configured yet. Adding a monitoring service helps you spot slow pages and fix speed issues before visitors notice.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/web-performance-monitoring-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
