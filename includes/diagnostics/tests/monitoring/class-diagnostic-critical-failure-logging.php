<?php
/**
 * Critical Failure Logging Diagnostic
 *
 * Checks whether critical failures are logged for troubleshooting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Monitoring
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Critical Failure Logging Diagnostic Class
 *
 * Verifies that logging is enabled for critical failures.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Critical_Failure_Logging extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'critical-failure-logging';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Logging of Critical Failures';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether critical failures are logged for diagnosis';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$log_errors = ini_get( 'log_errors' );
		$debug_log  = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;

		$logging_plugins = array(
			'wp-activity-log/wp-security-audit-log.php' => 'WP Activity Log',
			'simple-history/index.php'                 => 'Simple History',
			'error-log-monitor/error-log-monitor.php'  => 'Error Log Monitor',
		);

		$active_logs = array();
		foreach ( $logging_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_logs[] = $plugin_name;
			}
		}

		$stats['php_log_errors'] = $log_errors ? 'enabled' : 'disabled';
		$stats['wp_debug_log']   = $debug_log ? 'enabled' : 'disabled';
		$stats['logging_tools']  = ! empty( $active_logs ) ? implode( ', ', $active_logs ) : 'none';

		if ( ! $log_errors && ! $debug_log && empty( $active_logs ) ) {
			$issues[] = __( 'No logging system detected for critical failures', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'When failures are logged, it is easier to fix problems quickly and prevent them from repeating. Logging is like a black box for your site.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/critical-failure-logging?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
