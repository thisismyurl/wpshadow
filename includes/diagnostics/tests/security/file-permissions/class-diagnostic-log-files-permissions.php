<?php
/**
 * Log Files Permissions Diagnostic
 *
 * Checks if log files have secure permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Log Files Permissions Diagnostic Class
 *
 * Verifies log files aren't publicly accessible.
 * Like checking that your security camera recordings are kept private.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Log_Files_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'log-files-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Log Files Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if log files have secure permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the log files permissions diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if permission issues detected, null otherwise.
	 */
	public static function check() {
		$vulnerable_logs = array();

		// Common log file locations.
		$log_locations = array(
			'debug.log'          => WP_CONTENT_DIR . '/debug.log',
			'error_log'          => ABSPATH . 'error_log',
			'php_errors.log'     => ABSPATH . 'php_errors.log',
			'wp-errors.log'      => WP_CONTENT_DIR . '/wp-errors.log',
			'.htaccess.log'      => ABSPATH . '.htaccess.log',
		);

		foreach ( $log_locations as $log_name => $log_path ) {
			if ( file_exists( $log_path ) ) {
				$perms = fileperms( $log_path );
				$perms_octal = substr( sprintf( '%o', $perms ), -4 );

				// Check if file is world-readable (others can read).
				$others_can_read = ( $perms & 0004 ) !== 0;

				if ( $others_can_read ) {
					$vulnerable_logs[] = array(
						'file'        => $log_name,
						'path'        => $log_path,
						'permissions' => $perms_octal,
					);
				}

				// Check if log file is accessible via web (in web-accessible directory).
				$web_accessible = self::is_web_accessible( $log_path );
				if ( $web_accessible ) {
					$vulnerable_logs[] = array(
						'file'           => $log_name,
						'path'           => $log_path,
						'permissions'    => $perms_octal,
						'web_accessible' => true,
					);
				}
			}
		}

		// Check for WP_DEBUG_LOG being enabled with debug.log in web root.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$debug_log = WP_CONTENT_DIR . '/debug.log';
			if ( file_exists( $debug_log ) ) {
				$size = filesize( $debug_log );
				$size_mb = $size / 1024 / 1024;

				// Large debug log is a problem.
				if ( $size_mb > 10 ) {
					$vulnerable_logs[] = array(
						'file'        => 'debug.log',
						'path'        => $debug_log,
						'size_mb'     => $size_mb,
						'too_large'   => true,
					);
				}
			}
		}

		if ( empty( $vulnerable_logs ) ) {
			return null; // No vulnerable log files found.
		}

		$issues = array();
		$web_accessible_count = 0;
		$readable_count = 0;
		$large_count = 0;

		foreach ( $vulnerable_logs as $log ) {
			if ( isset( $log['web_accessible'] ) ) {
				++$web_accessible_count;
				$issues[] = sprintf(
					/* translators: %s: log file name */
					__( '%s is accessible via web browser', 'wpshadow' ),
					$log['file']
				);
			} elseif ( isset( $log['too_large'] ) ) {
				++$large_count;
				$issues[] = sprintf(
					/* translators: 1: log file name, 2: file size in MB */
					__( '%1$s is very large (%2$s MB)', 'wpshadow' ),
					$log['file'],
					number_format_i18n( $log['size_mb'], 1 )
				);
			} else {
				++$readable_count;
				$issues[] = sprintf(
					/* translators: 1: log file name, 2: permissions */
					__( '%1$s has permissive permissions (%2$s)', 'wpshadow' ),
					$log['file'],
					$log['permissions']
				);
			}
		}

		$severity = 'low';
		$threat_level = 35;

		if ( $web_accessible_count > 0 ) {
			$severity = 'high';
			$threat_level = 75;
		} elseif ( $large_count > 0 ) {
			$severity = 'medium';
			$threat_level = 50;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: list of issues */
				__( 'Some log files have security or size issues (like leaving your security camera recordings where anyone can watch them). Log files often contain sensitive information like database errors, user activity, or system details. Issues: %s. Change log file permissions to 0640 or move them outside the web-accessible directory. Consider rotating or deleting large log files.', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/log-file-security',
			'context'      => array(
				'vulnerable_logs'      => $vulnerable_logs,
				'web_accessible_count' => $web_accessible_count,
				'readable_count'       => $readable_count,
				'large_count'          => $large_count,
			),
		);
	}

	/**
	 * Check if file is in a web-accessible directory.
	 *
	 * @since 1.6093.1200
	 * @param  string $file_path File path to check.
	 * @return bool True if web-accessible.
	 */
	private static function is_web_accessible( $file_path ) {
		$real_path = realpath( $file_path );
		if ( ! $real_path ) {
			return false;
		}

		$real_abspath = realpath( ABSPATH );
		if ( ! $real_abspath ) {
			return false;
		}

		// If file is under ABSPATH and not in wp-includes or wp-admin, it's web-accessible.
		if ( 0 === strpos( $real_path, $real_abspath ) ) {
			// Check if it's in wp-content (usually web-accessible except uploads/.htaccess).
			if ( false !== strpos( $real_path, WP_CONTENT_DIR ) ) {
				return true;
			}

			// Check if it's directly in ABSPATH (root).
			if ( dirname( $real_path ) === $real_abspath ) {
				return true;
			}
		}

		return false;
	}
}
