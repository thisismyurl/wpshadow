<?php
/**
 * Error Logging Diagnostic
 *
 * Proper error logging captures PHP and WordPress errors silently to a
 * log file without exposing them to site visitors. Two failure modes are
 * common: errors displayed publicly (security/UX risk) and logging
 * completely disabled (operators cannot see faults occurring on the site).
 *
 * @package WPShadow
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
 * Diagnostic_Error_Logging Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Error_Logging extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'error-logging';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Error Logging';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that WordPress error logging is configured for silent capture (WP_DEBUG_LOG on, WP_DEBUG_DISPLAY off) so errors are recorded without being shown to visitors.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Whether this diagnostic is part of the core trusted set.
	 *
	 * @var bool
	 */
	protected static $is_core = true;

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'high';

	/**
	 * Run the diagnostic check.
	 *
	 * Evaluates three WP_DEBUG-related constants to determine the logging
	 * configuration, then raises the appropriate finding:
	 *
	 *  - WP_DEBUG true + WP_DEBUG_DISPLAY true  → errors visible to public (critical)
	 *  - WP_DEBUG false + WP_DEBUG_LOG false     → no logging at all (medium)
	 *  - WP_DEBUG true + WP_DEBUG_LOG true + WP_DEBUG_DISPLAY false → ideal (null)
	 *  - PHP log_errors ini also checked as fallback.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$wp_debug         = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$wp_debug_log     = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$wp_debug_display = defined( 'WP_DEBUG_DISPLAY' ) ? WP_DEBUG_DISPLAY : true; // WP default is true.

		// Worst case: errors are being displayed to the public.
		if ( $wp_debug && $wp_debug_display ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WP_DEBUG is enabled and WP_DEBUG_DISPLAY is on, which means PHP and WordPress errors are being printed on screen for all visitors. This exposes file paths, database information, and code structure to potential attackers.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'kb_link'      => 'https://wpshadow.com/kb/error-logging?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'wp_debug'         => $wp_debug,
					'wp_debug_log'     => $wp_debug_log,
					'wp_debug_display' => $wp_debug_display,
					'fix'              => __( 'In wp-config.php, set WP_DEBUG_DISPLAY to false and WP_DEBUG to false for production. If you need to debug, set WP_DEBUG_LOG to true so errors go to /wp-content/debug.log instead of the screen.', 'wpshadow' ),
				),
			);
		}

		// No logging at all: debugging is completely off.
		$php_log_errors = filter_var( ini_get( 'log_errors' ), FILTER_VALIDATE_BOOLEAN );

		if ( ! $wp_debug && ! $wp_debug_log && ! $php_log_errors ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Error logging is completely disabled. PHP errors, plugin conflicts, and database issues are occurring silently with no record, making it impossible to diagnose problems.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'kb_link'      => 'https://wpshadow.com/kb/error-logging?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'wp_debug'         => $wp_debug,
					'wp_debug_log'     => $wp_debug_log,
					'wp_debug_display' => $wp_debug_display,
					'fix'              => __( 'Add the following lines to wp-config.php to enable silent logging: define(\'WP_DEBUG\', true); define(\'WP_DEBUG_LOG\', true); define(\'WP_DEBUG_DISPLAY\', false); @ini_set(\'display_errors\', 0); This writes errors to wp-content/debug.log without displaying them to visitors.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
