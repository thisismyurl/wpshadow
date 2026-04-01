<?php
/**
 * Cross-Site Session Leakage Diagnostic
 *
 * Detects session data leakage across different domains, subdomains,
 * or security contexts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cross-Site Session Leakage Diagnostic Class
 *
 * Checks for:
 * - Cookie domain scope too broad
 * - Session cookies shared across subdomains
 * - SameSite attribute not set
 * - CORS headers exposing credentials
 * - Session data in cross-origin requests
 * - Referer header leaking session data
 *
 * Cross-site session leakage exposes authentication tokens and
 * sensitive session data to untrusted domains.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Cross_Site_Session_Leakage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'cross-site-session-leakage';

	/**
	 * The diagnostic title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Cross-Site Session Leakage';

	/**
	 * The diagnostic description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Detects session data leakage to cross-origin contexts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates cross-site session security.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Cookie domain scope.
		$cookie_domain_issue = self::check_cookie_domain_scope();
		if ( $cookie_domain_issue ) {
			$issues[] = __( 'Cookie domain set too broadly (session accessible from all subdomains)', 'wpshadow' );
		}

		// Check 2: SameSite attribute.
		$missing_samesite = self::check_samesite_attribute();
		if ( $missing_samesite ) {
			$issues[] = __( 'Session cookies missing SameSite attribute (CSRF risk)', 'wpshadow' );
		}

		// Check 3: CORS headers with credentials.
		$cors_credentials = self::check_cors_credentials_exposure();
		if ( $cors_credentials ) {
			$issues[] = __( 'CORS headers allow credentials from multiple origins', 'wpshadow' );
		}

		// Check 4: Session tokens in URLs.
		$tokens_in_urls = self::check_session_tokens_in_urls();
		if ( $tokens_in_urls ) {
			$issues[] = __( 'Session tokens may be exposed in URLs (referer header leakage)', 'wpshadow' );
		}

		// Check 5: Secure flag on HTTPS.
		$missing_secure = self::check_secure_flag();
		if ( $missing_secure ) {
			$issues[] = __( 'Session cookies missing Secure flag on HTTPS site (downgrade attack risk)', 'wpshadow' );
		}

		// Check 6: HttpOnly flag.
		$missing_httponly = self::check_httponly_flag();
		if ( $missing_httponly ) {
			$issues[] = __( 'Session cookies missing HttpOnly flag (XSS can steal cookies)', 'wpshadow' );
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d cross-site session leakage issue detected',
						'%d cross-site session leakage issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cross-site-session-leakage?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'issues' => $issues,
					'why'    => __(
						'Cross-site session leakage exposes authentication to untrusted domains. Overly broad cookie domains (.example.com) ' .
						'allow malicious subdomains to access auth cookies. Missing SameSite enables CSRF attacks where external sites trigger ' .
						'authenticated requests. CORS with Access-Control-Allow-Credentials:true from * exposes cookies to any origin. ' .
						'Session tokens in URLs leak via Referer header to external sites. Missing Secure flag on HTTPS enables cookie theft ' .
						'via protocol downgrade attacks. Missing HttpOnly allows XSS to read cookies via JavaScript. According to OWASP, ' .
						'70% of CSRF attacks exploit missing SameSite. Session leakage is particularly dangerous because it enables silent ' .
						'unauthorized access without user awareness.',
						'wpshadow'
					),
					'recommendation' => __(
						'Set cookies for specific subdomain only (admin.example.com not .example.com). Always use SameSite=Strict or Lax. ' .
						'Never use Access-Control-Allow-Credentials with wildcard origins - specify exact domains. Never put session tokens ' .
						'in URLs - use headers only. Always set Secure flag on HTTPS sites. Always set HttpOnly flag. Use COOKIE_DOMAIN constant ' .
						'in wp-config.php. Implement Content Security Policy. Use Referrer-Policy: no-referrer for sensitive pages. ' .
						'Test with browser DevTools Network tab to verify cookie attributes.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'session-management',
				'session-leakage-guide'
			);

			return $finding;
		}

		return null;
	}

	/**
	 * Check cookie domain scope.
	 *
	 * @since 0.6093.1200
	 * @return bool True if too broad.
	 */
	private static function check_cookie_domain_scope() {
		// Check COOKIE_DOMAIN constant.
		if ( defined( 'COOKIE_DOMAIN' ) ) {
			$domain = COOKIE_DOMAIN;
			// If starts with dot, it's for all subdomains.
			if ( str_starts_with( $domain, '.' ) ) {
				return true;
			}
		}

		// Check session.cookie_domain.
		$session_domain = ini_get( 'session.cookie_domain' );
		if ( ! empty( $session_domain ) && str_starts_with( $session_domain, '.' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check SameSite attribute.
	 *
	 * @since 0.6093.1200
	 * @return bool True if missing.
	 */
	private static function check_samesite_attribute() {
		// Check PHP ini setting.
		$samesite = ini_get( 'session.cookie_samesite' );

		if ( empty( $samesite ) || 'None' === $samesite ) {
			return true;
		}

		// Check WordPress constants.
		if ( ! defined( 'COOKIE_DOMAIN' ) && ! defined( 'COOKIEPATH' ) ) {
			// No explicit cookie configuration, likely missing SameSite.
			return true;
		}

		return false;
	}

	/**
	 * Check CORS credentials exposure.
	 *
	 * @since 0.6093.1200
	 * @return bool True if exposed.
	 */
	private static function check_cors_credentials_exposure() {
		// Check for CORS implementation in theme/plugins.
		$theme_dir = get_stylesheet_directory();
		$pattern = '/header\s*\(\s*["\']Access-Control-Allow-Credentials:\s*true/i';
		$wildcard_pattern = '/header\s*\(\s*["\']Access-Control-Allow-Origin:\s*\*/i';

		$php_files = self::get_php_files( $theme_dir, 20 );
		$has_credentials = false;
		$has_wildcard = false;

		foreach ( $php_files as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );

			if ( preg_match( $pattern, $content ) ) {
				$has_credentials = true;
			}
			if ( preg_match( $wildcard_pattern, $content ) ) {
				$has_wildcard = true;
			}
		}

		// Vulnerable if both credentials and wildcard origin.
		return $has_credentials && $has_wildcard;
	}

	/**
	 * Check for session tokens in URLs.
	 *
	 * @since 0.6093.1200
	 * @return bool True if found.
	 */
	private static function check_session_tokens_in_urls() {
		// Check for common patterns of tokens in GET params.
		$theme_dir = get_stylesheet_directory();
		$pattern = '/\$_GET\[["\'](?:token|session|auth)["\'\]]/i';

		$php_files = self::get_php_files( $theme_dir, 20 );
		foreach ( $php_files as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );
			if ( preg_match( $pattern, $content ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check Secure flag.
	 *
	 * @since 0.6093.1200
	 * @return bool True if missing on HTTPS.
	 */
	private static function check_secure_flag() {
		// Only matters on HTTPS sites.
		if ( ! is_ssl() ) {
			return false;
		}

		// Check session.cookie_secure.
		$secure = ini_get( 'session.cookie_secure' );

		if ( empty( $secure ) || '0' === $secure ) {
			return true;
		}

		return false;
	}

	/**
	 * Check HttpOnly flag.
	 *
	 * @since 0.6093.1200
	 * @return bool True if missing.
	 */
	private static function check_httponly_flag() {
		// Check session.cookie_httponly.
		$httponly = ini_get( 'session.cookie_httponly' );

		if ( empty( $httponly ) || '0' === $httponly ) {
			return true;
		}

		return false;
	}

	/**
	 * Get PHP files from directory.
	 *
	 * @since 0.6093.1200
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
