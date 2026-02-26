<?php
/**
 * Custom Code Standards Diagnostic
 *
 * Checks if custom code follows WordPress coding standards and best practices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Code Standards Diagnostic Class
 *
 * Verifies that custom code follows WordPress coding standards and best
 * practices for security, performance, and maintainability.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Custom_Code_Standards extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-code-standards';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Code Standards';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom code follows WordPress coding standards and best practices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the custom code standards diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if standards issues detected, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$warnings     = array();
		$theme        = wp_get_theme();
		$theme_dir    = $theme->get_stylesheet_directory();
		$total_checks = 0;
		$issues_found = 0;

		// Get all PHP files in theme.
		$php_files = array_merge(
			glob( $theme_dir . '/*.php' ),
			glob( $theme_dir . '/inc/*.php' ),
			glob( $theme_dir . '/template-parts/*.php' )
		);

		$php_files = array_filter( $php_files, 'is_file' );

		if ( empty( $php_files ) ) {
			return null; // No custom code to check.
		}

		// Sample first 5 files for performance.
		$files_to_check = array_slice( $php_files, 0, 5 );

		foreach ( $files_to_check as $file ) {
			$content     = file_get_contents( $file );
			$file_issues = array();

			// Check for proper security practices.

			// Check for nonce verification on form submissions.
			if ( strpos( $content, '$_POST' ) !== false &&
				strpos( $content, 'wp_verify_nonce' ) === false &&
				strpos( $content, 'wp_nonce_field' ) === false ) {
				$file_issues[] = __( 'Form processing without nonce verification', 'wpshadow' );
			}

			// Check for sanitization.
			if ( strpos( $content, '$_GET' ) !== false &&
				strpos( $content, 'sanitize_' ) === false &&
				strpos( $content, 'absint' ) === false &&
				strpos( $content, 'intval' ) === false ) {
				$file_issues[] = __( 'GET parameters not sanitized', 'wpshadow' );
			}

			// Check for escaping on output.
			if ( strpos( $content, 'echo ' ) !== false &&
				strpos( $content, 'esc_' ) === false &&
				strpos( $content, 'wp_kses' ) === false ) {
				// May be false positive, but warn anyway.
				$file_issues[] = __( 'Possible unescaped output found', 'wpshadow' );
			}

			// Check for prepared SQL queries.
			if ( strpos( $content, '$wpdb->query' ) !== false ||
				strpos( $content, '$wpdb->get_' ) !== false ) {
				if ( strpos( $content, '$wpdb->prepare' ) === false ) {
					$file_issues[] = __( 'Database queries not using prepare()', 'wpshadow' );
				}
			}

			// Check for documentation.
			if ( ! preg_match( '/\/\*\*[\s\S]*?@since/', $content ) ) {
				$file_issues[] = __( 'Functions missing PHPDoc blocks with @since', 'wpshadow' );
			}

			// Check for proper spacing/formatting.
			if ( ( preg_match( '/if\s*\(/', $content ) > 0 || preg_match( '/for\s*\(/', $content ) > 0 ) && preg_match( '/if\(\$/', $content ) ) {
				// Check for space after control structures (WordPress standard).
				$file_issues[] = __( 'Missing space after control structures', 'wpshadow' );
			}

			// Check for hardcoded database table names.
			if ( strpos( $content, 'wp_posts' ) !== false &&
				strpos( $content, '$wpdb->posts' ) === false ) {
				$file_issues[] = __( 'Hardcoded table names should use $wpdb globals', 'wpshadow' );
			}

			// Check for capability checks.
			if ( strpos( $content, '$_GET' ) !== false ||
				strpos( $content, '$_POST' ) !== false ) {
				if ( strpos( $content, 'current_user_can' ) === false &&
					strpos( $content, 'is_admin' ) === false ) {
					$file_issues[] = __( 'No capability check found for admin functions', 'wpshadow' );
				}
			}

			// Check for text domain consistency.
			if ( strpos( $content, '__(' ) !== false ||
				strpos( $content, '_e(' ) !== false ) {
				if ( strpos( $content, "'wpshadow'" ) === false &&
					strpos( $content, '"wpshadow"' ) === false ) {
					$file_issues[] = __( 'Translatable strings use different text domain', 'wpshadow' );
				}
			}

			if ( ! empty( $file_issues ) ) {
				++$total_checks;
				$issues_found += count( $file_issues );
				foreach ( $file_issues as $issue ) {
					$issues[] = basename( $file ) . ': ' . $issue;
				}
			}
		}

		// If significant issues found.
		if ( $issues_found > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Custom code has %d standards violations found', 'wpshadow' ),
					$issues_found
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-code-standards',
				'context'      => array(
					'theme_name'    => $theme->get( 'Name' ),
					'files_checked' => count( $files_to_check ),
					'total_issues'  => $issues_found,
					'sample_issues' => array_slice( $issues, 0, 5 ),
				),
			);
		}

		// If some issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom code has standards recommendations: ', 'wpshadow' ) . implode( ', ', array_slice( $issues, 0, 3 ) ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-code-standards',
				'context'      => array(
					'theme_name'    => $theme->get( 'Name' ),
					'files_checked' => count( $files_to_check ),
					'total_issues'  => $issues_found,
					'issues'        => $issues,
				),
			);
		}

		return null; // Custom code follows standards.
	}
}
