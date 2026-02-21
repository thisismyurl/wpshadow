<?php
/**
 * Cross-Site Session Leakage Treatment
 *
 * Detects session data leakage across different domains, subdomains,
 * or security contexts.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2108
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cross-Site Session Leakage Treatment Class
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
 * @since 1.2033.2108
 */
class Treatment_Cross_Site_Session_Leakage extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $slug = 'cross-site-session-leakage';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $title = 'Cross-Site Session Leakage';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $description = 'Detects session data leakage to cross-origin contexts';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Validates cross-site session security.
	 *
	 * @since  1.2033.2108
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Cross_Site_Session_Leakage' );
	}

	/**
	 * Check cookie domain scope.
	 *
	 * @since  1.2033.2108
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
	 * @since  1.2033.2108
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
	 * @since  1.2033.2108
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
	 * @since  1.2033.2108
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
	 * @since  1.2033.2108
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
	 * @since  1.2033.2108
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
	 * @since  1.2033.2108
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
