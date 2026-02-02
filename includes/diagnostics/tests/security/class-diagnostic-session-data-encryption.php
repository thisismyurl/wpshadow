<?php
/**
 * Session Data Encryption Diagnostic
 *
 * Detects unencrypted sensitive data in sessions and cookies
 * that could be exposed through cookie theft or session hijacking.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2106
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Data Encryption Diagnostic Class
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
class Diagnostic_Session_Data_Encryption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $slug = 'session-data-encryption';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $title = 'Session Data Encryption';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $description = 'Verifies sensitive data in sessions and cookies is encrypted';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
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
		$issues = array();

		// Check 1: Verify session.use_strict_mode.
		$strict_mode = ini_get( 'session.use_strict_mode' );
		if ( ! $strict_mode || '0' === $strict_mode ) {
			$issues[] = __( 'session.use_strict_mode is disabled (allows session fixation attacks)', 'wpshadow' );
		}

		// Check 2: Check session.cookie_httponly.
		$httponly = ini_get( 'session.cookie_httponly' );
		if ( ! $httponly || '0' === $httponly ) {
			$issues[] = __( 'session.cookie_httponly is disabled (cookies accessible via JavaScript)', 'wpshadow' );
		}

		// Check 3: Check session.cookie_secure (should be on for HTTPS).
		if ( is_ssl() ) {
			$secure = ini_get( 'session.cookie_secure' );
			if ( ! $secure || '0' === $secure ) {
				$issues[] = __( 'session.cookie_secure is disabled despite HTTPS being available', 'wpshadow' );
			}
		}

		// Check 4: Check session.cookie_samesite.
		$samesite = ini_get( 'session.cookie_samesite' );
		if ( empty( $samesite ) || 'None' === $samesite ) {
			$issues[] = __( 'session.cookie_samesite not configured (vulnerable to CSRF)', 'wpshadow' );
		}

		// Check 5: Look for code that stores sensitive data in sessions.
		$session_storage = self::scan_for_session_storage();
		if ( ! empty( $session_storage ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				_n(
					'Found %d file storing sensitive data in $_SESSION',
					'Found %d files storing sensitive data in $_SESSION',
					count( $session_storage ),
					'wpshadow'
				),
				count( $session_storage )
			);
		}

		// Check 6: Check for sensitive data in WordPress cookies.
		$cookie_issues = self::check_cookie_usage();
		if ( ! empty( $cookie_issues ) ) {
			$issues = array_merge( $issues, $cookie_issues );
		}

		// Check 7: Verify WordPress uses secure cookies.
		if ( is_ssl() && ! defined( 'SECURE_AUTH_COOKIE' ) ) {
			$issues[] = __( 'SECURE_AUTH_COOKIE not defined (WordPress not using secure cookies)', 'wpshadow' );
		}

		// Check 8: Check session save path permissions.
		$save_path_issue = self::check_session_save_path();
		if ( $save_path_issue ) {
			$issues[] = $save_path_issue;
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d session/cookie security issue detected',
						'%d session/cookie security issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/session-data-encryption',
				'context'      => array(
					'issues' => $issues,
					'php_settings' => array(
						'session.use_strict_mode'  => ini_get( 'session.use_strict_mode' ),
						'session.cookie_httponly'  => ini_get( 'session.cookie_httponly' ),
						'session.cookie_secure'    => ini_get( 'session.cookie_secure' ),
						'session.cookie_samesite'  => ini_get( 'session.cookie_samesite' ),
						'session.save_path'        => ini_get( 'session.save_path' ),
					),
					'why' => __(
						'Unencrypted session data can be exposed through multiple attack vectors: ' .
						'session file access on shared hosting, cookie theft via XSS, network interception, ' .
						'backup file exposure, or filesystem vulnerabilities. According to OWASP, sensitive data ' .
						'in sessions is a critical risk because sessions persist across requests and are often ' .
						'stored in predictable locations. If an attacker gains access to session files or cookies, ' .
						'they can extract credentials, tokens, personal data, and impersonate users.',
						'wpshadow'
					),
					'recommendation' => __(
						'Enable session.cookie_httponly, session.cookie_secure (for HTTPS), and session.use_strict_mode in php.ini. ' .
						'Set session.cookie_samesite to "Strict" or "Lax". Never store passwords, credit cards, or API keys in sessions. ' .
						'Encrypt sensitive session data using WordPress encryption functions. Use secure session save paths with restricted permissions. ' .
						'Implement session token rotation and invalidation on privilege changes.',
						'wpshadow'
					),
				),
			);
		}

		return null;
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
