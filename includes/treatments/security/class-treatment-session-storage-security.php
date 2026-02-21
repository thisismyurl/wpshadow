<?php
/**
 * Session Storage Security Treatment
 *
 * Detects insecure session storage configurations that expose
 * session data to unauthorized access.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2109
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Storage Security Treatment Class
 *
 * Checks for:
 * - Session files in world-readable directories
 * - Session save path with weak permissions
 * - Sessions stored in /tmp on shared hosting
 * - Database session storage without encryption
 * - Session data in web-accessible locations
 * - Session file cleanup not configured
 *
 * Insecure session storage allows attackers to read session files
 * directly from the filesystem or database, bypassing authentication.
 *
 * @since 1.2033.2109
 */
class Treatment_Session_Storage_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2109
	 * @var   string
	 */
	protected static $slug = 'session-storage-security';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2109
	 * @var   string
	 */
	protected static $title = 'Session Storage Security';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2109
	 * @var   string
	 */
	protected static $description = 'Verifies secure session storage configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2109
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Validates session storage security.
	 *
	 * @since  1.2033.2109
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Storage_Security' );
	}

	/**
	 * Check session save path permissions.
	 *
	 * @since  1.2033.2109
	 * @return string|null Issue description or null.
	 */
	private static function check_session_save_path_permissions() {
		$save_path = session_save_path();
		
		if ( empty( $save_path ) ) {
			$save_path = sys_get_temp_dir();
		}

		if ( ! is_dir( $save_path ) ) {
			return __( 'Session save path does not exist', 'wpshadow' );
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$perms = @fileperms( $save_path );
		if ( false === $perms ) {
			return __( 'Cannot read session directory permissions', 'wpshadow' );
		}

		$perms_octal = substr( sprintf( '%o', $perms ), -3 );
		
		// Should be 0700 or 0600.
		if ( ! in_array( $perms_octal, array( '700', '600' ), true ) ) {
			return sprintf(
				/* translators: %s: permission mode */
				__( 'Session directory has weak permissions: %s (should be 0700)', 'wpshadow' ),
				$perms_octal
			);
		}

		return null;
	}

	/**
	 * Check shared /tmp usage.
	 *
	 * @since  1.2033.2109
	 * @return bool True if using shared tmp.
	 */
	private static function check_shared_tmp_usage() {
		$save_path = session_save_path();
		
		if ( empty( $save_path ) ) {
			$save_path = sys_get_temp_dir();
		}

		// Check if path is /tmp or /var/tmp.
		$shared_paths = array( '/tmp', '/var/tmp', sys_get_temp_dir() );
		
		foreach ( $shared_paths as $shared ) {
			if ( $save_path === $shared ) {
				// On shared hosting, this is a problem.
				// Check if we're on shared hosting (simplified).
				if ( function_exists( 'posix_getpwuid' ) ) {
					$process_user = posix_getpwuid( posix_geteuid() );
					if ( isset( $process_user['name'] ) && $process_user['name'] !== 'root' ) {
						return true; // Likely shared hosting.
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check web-accessible sessions.
	 *
	 * @since  1.2033.2109
	 * @return bool True if web-accessible.
	 */
	private static function check_web_accessible_sessions() {
		$save_path = session_save_path();
		
		if ( empty( $save_path ) ) {
			return false;
		}

		// Check if save path is within ABSPATH.
		if ( str_starts_with( $save_path, ABSPATH ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check session garbage collection.
	 *
	 * @since  1.2033.2109
	 * @return bool True if disabled.
	 */
	private static function check_session_gc() {
		$gc_probability = ini_get( 'session.gc_probability' );
		$gc_divisor = ini_get( 'session.gc_divisor' );
		
		// If probability is 0, GC is disabled.
		if ( '0' === $gc_probability ) {
			return true;
		}

		// If probability/divisor ratio is too low (< 1%).
		if ( $gc_divisor > 0 && ( $gc_probability / $gc_divisor ) < 0.01 ) {
			return true;
		}

		return false;
	}

	/**
	 * Check database session encryption.
	 *
	 * @since  1.2033.2109
	 * @return bool True if unencrypted.
	 */
	private static function check_database_session_encryption() {
		// Check if using database sessions.
		$handler = ini_get( 'session.save_handler' );
		
		if ( 'user' !== $handler && 'redis' !== $handler && 'memcached' !== $handler ) {
			return false; // Not using DB sessions.
		}

		// Check for encryption in session handling code.
		$theme_dir = get_stylesheet_directory();
		$pattern = '/session.*(?:encrypt|sodium_crypto)/i';

		$php_files = self::get_php_files( $theme_dir, 20 );
		foreach ( $php_files as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );
			if ( preg_match( $pattern, $content ) ) {
				return false; // Encryption found.
			}
		}

		return true; // No encryption found.
	}

	/**
	 * Check session handler security.
	 *
	 * @since  1.2033.2109
	 * @return string|null Issue description or null.
	 */
	private static function check_session_handler_security() {
		$handler = ini_get( 'session.save_handler' );
		
		if ( 'files' === $handler ) {
			return null; // Default handler, covered by other checks.
		}

		if ( 'redis' === $handler || 'memcached' === $handler ) {
			// Check if authentication is configured.
			$save_path = ini_get( 'session.save_path' );
			if ( ! str_contains( $save_path, 'auth=' ) && ! str_contains( $save_path, 'password=' ) ) {
				return sprintf(
					/* translators: %s: handler type */
					__( '%s session handler lacks authentication', 'wpshadow' ),
					ucfirst( $handler )
				);
			}
		}

		return null;
	}

	/**
	 * Get PHP files from directory.
	 *
	 * @since  1.2033.2109
	 * @param  string $dir Directory path.
	 * @param  int    $limit Maximum files.
	 * @return array File paths.
	 */
	private static function get_php_files( $dir, $limit = 50 ) {
		$files = array();
		$count = 0;

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $count >= $limit ) {
				break;
			}
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$files[] = $file->getPathname();
				$count++;
			}
		}

		return $files;
	}
}
