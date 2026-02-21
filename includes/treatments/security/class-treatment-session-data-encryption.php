<?php
/**
 * Session Data Encryption Treatment
 *
 * Detects unencrypted sensitive data in sessions and cookies
 * that could be exposed through cookie theft or session hijacking.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2106
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Data Encryption Treatment Class
 *
 * Checks for:
 * - Sensitive data stored in PHP sessions without encryption
 * - Cookie values containing sensitive information
 * - Session tokens stored in plaintext
 * - Personal data in auth cookies
 * - Credit card or API key data in sessions
 *
 * According to OWASP, sensitive data in sessions is a critical
 * vulnerability because sessions are often stored in shared hosting
 * environments or transmitted over insecure connections.
 *
 * @since 1.2033.2106
 */
class Treatment_Session_Data_Encryption extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $slug = 'session-data-encryption';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $title = 'Session Data Encryption';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $description = 'Verifies sensitive data in sessions and cookies is encrypted';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes session and cookie usage:
	 * 1. Checks session file permissions
	 * 2. Looks for sensitive data patterns in session vars
	 * 3. Validates cookie security flags
	 * 4. Checks for encryption of stored credentials
	 *
	 * @since  1.2033.2106
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Data_Encryption' );
	}

	/**
	 * Scan code for sensitive data storage in sessions.
	 *
	 * @since  1.2033.2106
	 * @return array Files with session storage issues.
	 */
	private static function scan_for_session_storage() {
		$found = array();
		
		// Patterns indicating sensitive data in sessions.
		$sensitive_patterns = array(
			'/\$_SESSION\[[^\]]*password[^\]]*\]\s*=/' => 'Password stored in session',
			'/\$_SESSION\[[^\]]*api[_-]?key[^\]]*\]\s*=/' => 'API key stored in session',
			'/\$_SESSION\[[^\]]*secret[^\]]*\]\s*=/' => 'Secret stored in session',
			'/\$_SESSION\[[^\]]*credit[_-]?card[^\]]*\]\s*=/' => 'Credit card in session',
			'/\$_SESSION\[[^\]]*token[^\]]*\]\s*=/' => 'Token stored in session',
		);

		$theme_dir = get_stylesheet_directory();
		$plugin_dir = WP_PLUGIN_DIR;

		// Scan theme.
		$theme_files = self::get_php_files( $theme_dir, 30 );
		foreach ( $theme_files as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );
			foreach ( $sensitive_patterns as $pattern => $desc ) {
				if ( preg_match( $pattern, $content ) ) {
					$found[] = str_replace( ABSPATH, '', $file );
					break;
				}
			}
		}

		// Scan top 5 plugins.
		$active_plugins = array_slice( get_option( 'active_plugins', array() ), 0, 5 );
		foreach ( $active_plugins as $plugin ) {
			$plugin_path = $plugin_dir . '/' . dirname( $plugin );
			if ( is_dir( $plugin_path ) ) {
				$plugin_files = self::get_php_files( $plugin_path, 10 );
				foreach ( $plugin_files as $file ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					$content = file_get_contents( $file );
					foreach ( $sensitive_patterns as $pattern => $desc ) {
						if ( preg_match( $pattern, $content ) ) {
							$found[] = str_replace( ABSPATH, '', $file );
							break 2;
						}
					}
				}
			}
		}

		return array_unique( $found );
	}

	/**
	 * Check WordPress cookie usage.
	 *
	 * @since  1.2033.2106
	 * @return array Issues found.
	 */
	private static function check_cookie_usage() {
		$issues = array();

		// Check if cookies are set without proper flags.
		if ( ! empty( $_COOKIE ) ) {
			foreach ( $_COOKIE as $name => $value ) {
				// Check for WordPress auth cookies.
				if ( str_starts_with( $name, 'wordpress_logged_in_' ) || 
				     str_starts_with( $name, 'wordpress_' ) ) {
					// These should be handled by WordPress core.
					continue;
				}

				// Check for plugin cookies with sensitive-looking names.
				if ( preg_match( '/(api|key|token|secret|password)/i', $name ) ) {
					$issues[] = sprintf(
						/* translators: %s: cookie name */
						__( 'Cookie "%s" may contain sensitive data', 'wpshadow' ),
						$name
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Check session save path security.
	 *
	 * @since  1.2033.2106
	 * @return string|null Issue description or null.
	 */
	private static function check_session_save_path() {
		$save_path = ini_get( 'session.save_path' );
		
		if ( empty( $save_path ) ) {
			return null;
		}

		// Check if path exists and is readable.
		if ( ! is_dir( $save_path ) ) {
			return null;
		}

		// Check permissions (should be 0700 or 0600).
		$perms = fileperms( $save_path );
		$perms_oct = substr( sprintf( '%o', $perms ), -4 );

		if ( '0700' !== $perms_oct && '0600' !== $perms_oct ) {
			return sprintf(
				/* translators: 1: permissions, 2: path */
				__( 'Session save path has insecure permissions (%1$s): %2$s', 'wpshadow' ),
				$perms_oct,
				$save_path
			);
		}

		return null;
	}

	/**
	 * Get PHP files from directory.
	 *
	 * @since  1.2033.2106
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
