<?php
/**
 * Session Storage Security Diagnostic
 *
 * Detects insecure session storage configurations that expose
 * session data to unauthorized access.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2109
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Storage Security Diagnostic Class
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
class Diagnostic_Session_Storage_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2109
	 * @var   string
	 */
	protected static $slug = 'session-storage-security';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2109
	 * @var   string
	 */
	protected static $title = 'Session Storage Security';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2109
	 * @var   string
	 */
	protected static $description = 'Verifies secure session storage configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2109
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates session storage security.
	 *
	 * @since  1.2033.2109
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Session save path permissions.
		$path_issue = self::check_session_save_path_permissions();
		if ( $path_issue ) {
			$issues[] = $path_issue;
		}

		// Check 2: Shared /tmp usage.
		$shared_tmp = self::check_shared_tmp_usage();
		if ( $shared_tmp ) {
			$issues[] = __( 'Sessions stored in shared /tmp directory (accessible to other users on server)', 'wpshadow' );
		}

		// Check 3: Web-accessible session location.
		$web_accessible = self::check_web_accessible_sessions();
		if ( $web_accessible ) {
			$issues[] = __( 'Session files may be in web-accessible directory (direct download risk)', 'wpshadow' );
		}

		// Check 4: Session garbage collection.
		$gc_disabled = self::check_session_gc();
		if ( $gc_disabled ) {
			$issues[] = __( 'Session garbage collection disabled (old session files accumulate)', 'wpshadow' );
		}

		// Check 5: Database session encryption.
		$db_unencrypted = self::check_database_session_encryption();
		if ( $db_unencrypted ) {
			$issues[] = __( 'Database sessions not encrypted (session data readable in DB)', 'wpshadow' );
		}

		// Check 6: Session handler security.
		$handler_issue = self::check_session_handler_security();
		if ( $handler_issue ) {
			$issues[] = $handler_issue;
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d session storage security issue detected',
						'%d session storage security issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/session-storage-security',
				'context'      => array(
					'issues' => $issues,
					'why'    => __(
						'Insecure session storage directly exposes authentication tokens. Weak permissions (755, 777) allow any user on shared ' .
						'hosting to read session files. Shared /tmp means multiple sites can access each other\'s sessions. Web-accessible ' .
						'session directories enable direct download via guessed filenames (sess_abc123). Without garbage collection, old sessions ' .
						'persist indefinitely, expanding attack surface. Unencrypted database sessions expose tokens to anyone with DB access ' .
						'(backups, compromised plugins). According to NIST, session storage must be isolated per-user with 0600 permissions. ' .
						'Sucuri reports 42% of compromised sites had session files readable by other hosting accounts.',
						'wpshadow'
					),
					'recommendation' => __(
						'Set session.save_path to dedicated directory with 0700 permissions outside web root. Use session_save_path(\'/var/php-sessions/\' . get_current_user_id()). ' .
						'Never store sessions in /tmp on shared hosting - create private directory. Ensure session directory is outside public_html. ' .
						'Enable garbage collection: session.gc_probability=1, session.gc_divisor=100, session.gc_maxlifetime=1440. ' .
						'If using database sessions, encrypt data with sodium_crypto_secretbox(). Use Redis/Memcached with authentication. ' .
						'Add .htaccess deny rules to session directories. Monitor session file ownership.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'session-hardening',
				'session_storage_security'
			);

			return $finding;
		}

		return null;
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
