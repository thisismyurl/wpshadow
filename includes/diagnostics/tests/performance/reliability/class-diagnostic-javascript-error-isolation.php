<?php
/**
 * JavaScript Error Isolation Diagnostic
 *
 * Checks whether JavaScript errors are monitored and contained.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Reliability
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JavaScript Error Isolation Diagnostic Class
 *
 * Verifies that front-end errors are tracked and do not cascade.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Javascript_Error_Isolation extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-error-isolation';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Errors Break Entire Page Functionality';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if front-end errors are tracked and isolated';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$error_tools = array(
			'sentry/sentry.php' => 'Sentry',
			'rollbar/rollbar.php' => 'Rollbar',
			'wp-browser-errors/wp-browser-errors.php' => 'WP Browser Errors',
			'errorception/errorception.php' => 'Errorception',
		);

		$active_tools = array();
		foreach ( $error_tools as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_tools[] = $plugin_name;
			}
		}

		$stats['error_monitoring'] = ! empty( $active_tools ) ? implode( ', ', $active_tools ) : 'none';

		if ( empty( $active_tools ) ) {
			$issues[] = __( 'No front-end error monitoring detected to catch JavaScript failures', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'JavaScript errors can stop forms and buttons from working. Monitoring errors helps you catch problems early and keep key actions reliable.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/javascript-error-isolation',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
