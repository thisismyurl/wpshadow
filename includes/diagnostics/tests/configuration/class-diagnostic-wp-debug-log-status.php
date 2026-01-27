<?php
/**
 * Diagnostic: WP_DEBUG_LOG Status
 *
 * Checks if WP_DEBUG_LOG is enabled for error logging.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Wp_Debug_Log_Status
 *
 * Tests if error logging is configured.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Debug_Log_Status extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-debug-log-status';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WP_DEBUG_LOG Status';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if error logging is enabled for debugging';

	/**
	 * Check WP_DEBUG_LOG status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$wp_debug     = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;
		$wp_debug_log = defined( 'WP_DEBUG_LOG' ) ? WP_DEBUG_LOG : false;

		// If debug is enabled but logging is not, recommend enabling logging.
		if ( $wp_debug && ! $wp_debug_log ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WP_DEBUG is enabled but WP_DEBUG_LOG is not. Enable logging in wp-config.php to save errors to a log file instead of displaying them.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_debug_log_status',
				'meta'        => array(
					'wp_debug'     => $wp_debug,
					'wp_debug_log' => $wp_debug_log,
				),
			);
		}

		// If logging is enabled, check if log file is accessible.
		if ( $wp_debug_log ) {
			$log_path = WP_CONTENT_DIR . '/debug.log';

			if ( ! is_writable( dirname( $log_path ) ) ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'Log directory is not writable. Errors may not be logged. Check file permissions on wp-content/ directory.', 'wpshadow' ),
					'severity'    => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/wp_debug_log_status',
					'meta'        => array(
						'log_path'  => $log_path,
						'writable'  => false,
					),
				);
			}
		}

		return null;
	}
}
