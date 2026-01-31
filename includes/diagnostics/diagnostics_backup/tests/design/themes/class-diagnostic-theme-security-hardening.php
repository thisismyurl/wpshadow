<?php
/**
 * Theme Security Hardening Diagnostic
 *
 * Validates theme security best practices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Security Hardening Class
 *
 * Scans theme files for common security issues.
 * Checks for: eval(), base64_decode(), unsafe functions.
 *
 * @since 1.5029.1700
 */
class Diagnostic_Theme_Security_Hardening extends Diagnostic_Base {

	protected static $slug        = 'theme-security-hardening';
	protected static $title       = 'Theme Security Hardening';
	protected static $description = 'Validates theme security practices';
	protected static $family      = 'themes';

	public static function check() {
		$cache_key = 'wpshadow_theme_security';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$current_theme = wp_get_theme();
		$theme_dir = $current_theme->get_stylesheet_directory();
		$security_issues = array();

		// Dangerous functions to check for.
		$dangerous_functions = array( 'eval', 'base64_decode', 'gzinflate', 'str_rot13', 'assert', 'create_function' );

		// Scan theme PHP files.
		$php_files = glob( $theme_dir . '/*.php' );
		
		foreach ( $php_files as $file ) {
			if ( ! is_readable( $file ) ) {
				continue;
			}

			$content = file_get_contents( $file );
			$filename = basename( $file );

			foreach ( $dangerous_functions as $function ) {
				if ( preg_match( '/\b' . $function . '\s*\(/i', $content ) ) {
					$security_issues[] = array(
						'file' => $filename,
						'issue' => "Contains potentially dangerous function: {$function}()",
						'severity' => 'high',
					);
				}
			}

			// Check for direct database queries without $wpdb->prepare().
			if ( preg_match( '/\$wpdb->query\s*\(\s*["\'][^"\']*\$/', $content ) ) {
				$security_issues[] = array(
					'file' => $filename,
					'issue' => 'Potential SQL injection - query not using $wpdb->prepare()',
					'severity' => 'critical',
				);
			}

			// Limit scans.
			if ( count( $php_files ) > 20 ) {
				break;
			}
		}

		if ( ! empty( $security_issues ) ) {
			$critical_count = count( array_filter( $security_issues, function( $i ) {
				return 'critical' === $i['severity'];
			} ) );

			$threat_level = $critical_count > 0 ? 75 : 50;

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: theme name, 2: issue count */
					__( 'Theme "%1$s" has %2$d security issues. Review code for vulnerabilities.', 'wpshadow' ),
					$current_theme->get( 'Name' ),
					count( $security_issues )
				),
				'severity'     => $threat_level > 60 ? 'high' : 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/themes-security-hardening',
				'data'         => array(
					'theme_name' => $current_theme->get( 'Name' ),
					'security_issues' => array_slice( $security_issues, 0, 20 ),
					'total_issues' => count( $security_issues ),
					'critical_issues' => $critical_count,
				),
			);

			set_transient( $cache_key, $result, 7 * DAY_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 7 * DAY_IN_SECONDS );
		return null;
	}
}
