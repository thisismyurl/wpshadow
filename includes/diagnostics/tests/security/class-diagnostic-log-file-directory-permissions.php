<?php
/**
 * Log File Directory Permissions Diagnostic
 *
 * Checks log directory is writable if configured.
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
 * Log File Directory Permissions Diagnostic Class
 *
 * Verifies log directory permissions if debug logging configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Log_File_Directory_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'log-file-directory-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Log File Directory Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies log directory is writable if configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the permissions diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if permission issue detected, null otherwise.
	 */
	public static function check() {
		// Only check if debug logging is enabled.
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			return null;
		}

		// Determine log file path.
		$log_path = self::get_log_path();

		if ( ! $log_path ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Debug logging is enabled but log directory could not be determined.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/debug-log-directory-issue?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		$log_dir = dirname( $log_path );

		if ( ! is_dir( $log_dir ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: path */
					__( 'Log directory does not exist: %s', 'wpshadow' ),
					$log_dir
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/debug-log-directory-missing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		if ( ! is_writable( $log_dir ) ) {
			$perms = substr( sprintf( '%o', fileperms( $log_dir ) ), -4 );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: permissions */
					__( 'Log directory is not writable. Current permissions: %s', 'wpshadow' ),
					$perms
				),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/fix-log-directory-permissions?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'        => array(
					'current_permissions' => $perms,
					'expected_permissions' => '0755',
				),
			);
		}

		return null;
	}

	/**
	 * Get log file path.
	 *
	 * @since 0.6093.1200
	 * @return string|null Log file path or null.
	 */
	private static function get_log_path(): ?string {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			if ( is_string( WP_DEBUG_LOG ) ) {
				return WP_DEBUG_LOG;
			}

			// Default WordPress debug log path.
			return WP_CONTENT_DIR . '/debug.log';
		}

		return null;
	}
}
