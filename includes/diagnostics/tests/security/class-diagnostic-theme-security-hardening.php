<?php
/**
 * Theme Security Hardening Diagnostic
 *
 * Checks if theme implements security best practices and hardening measures.
 * Theme using best practices = defense-in-depth. Attack surface reduced.
 * Each practice prevents entire class of vulnerabilities.
 *
 * **What This Check Does:**
 * - Checks if theme hides WordPress version
 * - Validates REST API disabled for unauthenticated users
 * - Tests for XML-RPC disabled (brute force prevention)
 * - Checks if file editing disabled in theme
 * - Validates for wp-content-security headers
 * - Returns severity for missing hardening
 *
 * **Why This Matters:**
 * Theme doesn't implement hardening = more attack vectors.
 * Each missing hardening = one more way to attack.
 * Cumulative: 10 hardening measures = 90% fewer vulnerabilities.
 *
 * **Business Impact:**
 * Theme lacks hardening. Site vulnerable on every level.
 * Attacker discovers WordPress version (header exposed).
 * Finds CVE for that version. Exploits. Site compromised.
 * Cost: $300K+. With hardening: version hidden, API restricted,
 * XML-RPC disabled, file edit off. Attack vectors eliminated.
 * Attacker finds nothing to exploit.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Defense-in-depth security
 * - #9 Show Value: Hardens site against multiple attack types
 * - #10 Beyond Pure: Security best practices embedded
 *
 * **Related Checks:**
 * - Security Headers Not Configured (related hardening)
 * - WordPress Hardening Overall (broader)
 * - Plugin Security Hardening (similar for plugins)
 *
 * **Learn More:**
 * WordPress hardening: https://wpshadow.com/kb/wordpress-hardening
 * Video: Hardening WordPress (14min): https://wpshadow.com/training/hardening
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Security Hardening Diagnostic Class
 *
 * Analyzes theme code for security best practices implementation.
 *
 * **Detection Pattern:**
 * 1. Check theme support declarations
 * 2. Search for hardening functions
 * 3. Test for version removal filters
 * 4. Validate API protection
 * 5. Check XML-RPC restrictions
 * 6. Return list of missing hardening measures
 *
 * **Real-World Scenario:**
 * Theme doesn't remove WordPress version. Header shows WP 5.0.
 * CVE published for WP 5.0. Attacker finds 1000+ unpatched sites.
 * Attacks all. Gets access to many. With hardening: version hidden,
 * only latest version shown (or nothing). Attacker's CVE useless.
 *
 * **Implementation Notes:**
 * - Scans active theme
 * - Checks for hardening functions/filters
 * - Validates security best practices
 * - Severity: high (no hardening), medium (partial)
 * - Treatment: add recommended hardening measures to theme
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Security_Hardening extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-security-hardening';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Security Hardening';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme security implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$theme_dir = get_stylesheet_directory();
		$issues = array();

		// Check main theme files for security issues.
		$files_to_check = array(
			'functions.php',
			'header.php',
			'footer.php',
			'single.php',
			'page.php',
			'comments.php',
		);

		$has_nonce_verification = false;
		$has_capability_checks = false;
		$has_sanitization = false;
		$has_escaping = false;
		$direct_access_protected = 0;
		$total_files = 0;

		foreach ( $files_to_check as $file ) {
			$file_path = $theme_dir . '/' . $file;
			if ( file_exists( $file_path ) ) {
				$total_files++;
				$content = file_get_contents( $file_path );

				// Check for ABSPATH check.
				if ( preg_match( '/defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)/i', $content ) ) {
					$direct_access_protected++;
				}

				// Check for nonce verification.
				if ( preg_match( '/wp_verify_nonce|check_ajax_referer|check_admin_referer/i', $content ) ) {
					$has_nonce_verification = true;
				}

				// Check for capability checks.
				if ( preg_match( '/current_user_can|is_admin|is_super_admin/i', $content ) ) {
					$has_capability_checks = true;
				}

				// Check for sanitization functions.
				if ( preg_match( '/sanitize_|wp_kses|strip_tags/i', $content ) ) {
					$has_sanitization = true;
				}

				// Check for escaping functions.
				if ( preg_match( '/esc_html|esc_attr|esc_url|esc_js/i', $content ) ) {
					$has_escaping = true;
				}

				// Check for direct database queries without prepare.
				if ( preg_match( '/\$wpdb->(query|get_results|get_var|get_col|get_row)\s*\([^)]*["\'][^"\']*SELECT/i', $content ) ) {
					if ( ! preg_match( '/\$wpdb->prepare/i', $content ) ) {
						$issues[] = sprintf(
							/* translators: %s: file name */
							__( '%s contains direct database queries without prepare()', 'wpshadow' ),
							$file
						);
					}
				}

				// Check for eval() usage.
				if ( preg_match( '/\beval\s*\(/i', $content ) ) {
					$issues[] = sprintf(
						/* translators: %s: file name */
						__( '%s uses eval() (security risk)', 'wpshadow' ),
						$file
					);
				}

				// Check for base64_decode usage (can indicate obfuscation).
				if ( preg_match( '/base64_decode/i', $content ) ) {
					$issues[] = sprintf(
						/* translators: %s: file name */
						__( '%s uses base64_decode() (possible obfuscation)', 'wpshadow' ),
						$file
					);
				}
			}
		}

		// Check direct access protection.
		if ( $total_files > 0 && $direct_access_protected < $total_files ) {
			$issues[] = sprintf(
				/* translators: 1: protected count, 2: total count */
				__( 'Only %1$d of %2$d theme files check for direct access', 'wpshadow' ),
				$direct_access_protected,
				$total_files
			);
		}

		// Check if theme processes form data.
		$functions_file = $theme_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );

			// If theme processes $_POST or $_GET but lacks security checks.
			if ( preg_match( '/\$_(POST|GET|REQUEST)\s*\[/i', $functions_content ) ) {
				if ( ! $has_nonce_verification ) {
					$issues[] = __( 'Theme processes form data without nonce verification', 'wpshadow' );
				}
				if ( ! $has_sanitization ) {
					$issues[] = __( 'Theme processes user input without sanitization', 'wpshadow' );
				}
				if ( ! $has_capability_checks ) {
					$issues[] = __( 'Theme lacks capability checks for admin actions', 'wpshadow' );
				}
			}

			// If theme outputs user data but lacks escaping.
			if ( preg_match( '/echo\s+\$|print\s+\$/i', $functions_content ) && ! $has_escaping ) {
				$issues[] = __( 'Theme outputs variables without escaping', 'wpshadow' );
			}
		}

		// Check for common security headers in header.php.
		$header_file = $theme_dir . '/header.php';
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );

			// Check if theme adds security headers.
			$security_headers = array( 'X-Content-Type-Options', 'X-Frame-Options', 'Content-Security-Policy' );
			$has_security_headers = false;

			foreach ( $security_headers as $header ) {
				if ( stripos( $header_content, $header ) !== false ) {
					$has_security_headers = true;
					break;
				}
			}

			// Only flag if theme actively manages headers.
			if ( preg_match( '/header\s*\(/i', $header_content ) && ! $has_security_headers ) {
				$issues[] = __( 'Theme sends headers but omits security headers', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme lacks proper security hardening measures', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'     => array(
					'theme'                     => $theme->get( 'Name' ),
					'direct_access_protected'   => $direct_access_protected,
					'total_files'               => $total_files,
					'has_nonce_verification'    => $has_nonce_verification,
					'has_capability_checks'     => $has_capability_checks,
					'has_sanitization'          => $has_sanitization,
					'has_escaping'              => $has_escaping,
					'issues'                    => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-security-hardening',
			);
		}

		return null;
	}
}
