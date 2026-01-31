<?php
/**
 * Plugin Admin Page Security Diagnostic
 *
 * Detects plugins with admin pages that have unescaped output,
 * identifying potential XSS vulnerabilities in plugin admin interfaces.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1725
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Admin Page Security Diagnostic Class
 *
 * Scans plugin admin pages for common security issues:
 * - Unescaped output (XSS vulnerabilities)
 * - Missing nonce verification
 * - Direct $_GET/$_POST access without sanitization
 * - Missing capability checks
 *
 * @since 1.6028.1725
 */
class Diagnostic_Plugin_Admin_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1725
	 * @var   string
	 */
	protected static $slug = 'plugin-admin-page-security';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1725
	 * @var   string
	 */
	protected static $title = 'Plugin Admin Page Security';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1725
	 * @var   string
	 */
	protected static $description = 'Detects security issues in plugin admin pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1725
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Cache duration (12 hours)
	 *
	 * @since 1.6028.1725
	 * @var   int
	 */
	private const CACHE_DURATION = 43200;

	/**
	 * Patterns indicating security issues
	 *
	 * @since 1.6028.1725
	 * @var   array
	 */
	private const SECURITY_PATTERNS = array(
		'unescaped_output' => array(
			'/echo\s+\$_(?:GET|POST|REQUEST)\[/',
			'/print\s+\$_(?:GET|POST|REQUEST)\[/',
			'/<\?=\s*\$_(?:GET|POST|REQUEST)\[/',
		),
		'missing_nonce'    => array(
			'/\$_POST.*wp_verify_nonce/',
		),
		'direct_access'    => array(
			'/\$_(?:GET|POST|REQUEST)\[(?!\s*[\'"]action[\'"])[^\]]+\]\s*(?!.*(?:sanitize|esc_|wp_unslash))/',
		),
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Scans plugin admin page files for security vulnerabilities.
	 *
	 * @since  1.6028.1725
	 * @return array|null Finding array if security issues found, null otherwise.
	 */
	public static function check() {
		$cached = get_transient( 'wpshadow_plugin_admin_security_check' );
		if ( false !== $cached ) {
			return $cached;
		}

		$analysis = self::analyze_plugin_admin_security();

		if ( empty( $analysis['vulnerable_files'] ) ) {
			set_transient( 'wpshadow_plugin_admin_security_check', null, self::CACHE_DURATION );
			return null;
		}

		$result = self::build_finding( $analysis );

		set_transient( 'wpshadow_plugin_admin_security_check', $result, self::CACHE_DURATION );

		return $result;
	}

	/**
	 * Analyze plugin admin pages for security issues.
	 *
	 * @since  1.6028.1725
	 * @return array {
	 *     Analysis results.
	 *
	 *     @type array $vulnerable_files List of files with security issues.
	 *     @type int   $total_scanned    Total files scanned.
	 *     @type int   $issue_count      Total security issues found.
	 * }
	 */
	private static function analyze_plugin_admin_security(): array {
		$vulnerable_files = array();
		$total_scanned    = 0;
		$issue_count      = 0;

		// Get active plugins.
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $active_plugins as $plugin_file ) {
			$plugin_dir = dirname( WP_PLUGIN_DIR . '/' . $plugin_file );

			// Look for admin files.
			$admin_files = self::find_admin_files( $plugin_dir );

			foreach ( $admin_files as $file_path ) {
				++$total_scanned;

				$issues = self::scan_file_for_issues( $file_path );

				if ( ! empty( $issues ) ) {
					$vulnerable_files[] = array(
						'file'   => str_replace( WP_PLUGIN_DIR, '', $file_path ),
						'plugin' => dirname( $plugin_file ),
						'issues' => $issues,
					);
					$issue_count += count( $issues );
				}
			}
		}

		return array(
			'vulnerable_files' => $vulnerable_files,
			'total_scanned'    => $total_scanned,
			'issue_count'      => $issue_count,
		);
	}

	/**
	 * Find admin-related PHP files in plugin directory.
	 *
	 * @since  1.6028.1725
	 * @param  string $plugin_dir Plugin directory path.
	 * @return array Array of admin file paths.
	 */
	private static function find_admin_files( string $plugin_dir ): array {
		$admin_files = array();

		if ( ! is_dir( $plugin_dir ) ) {
			return $admin_files;
		}

		// Common admin file patterns.
		$admin_patterns = array(
			$plugin_dir . '/admin/*.php',
			$plugin_dir . '/includes/admin/*.php',
			$plugin_dir . '/*-admin.php',
			$plugin_dir . '/admin.php',
		);

		foreach ( $admin_patterns as $pattern ) {
			$files = glob( $pattern );
			if ( $files ) {
				$admin_files = array_merge( $admin_files, $files );
			}
		}

		return array_unique( $admin_files );
	}

	/**
	 * Scan file for security issues.
	 *
	 * @since  1.6028.1725
	 * @param  string $file_path File path to scan.
	 * @return array Array of security issues found.
	 */
	private static function scan_file_for_issues( string $file_path ): array {
		$issues = array();

		$content = file_get_contents( $file_path );
		if ( false === $content ) {
			return $issues;
		}

		// Check for unescaped output.
		foreach ( self::SECURITY_PATTERNS['unescaped_output'] as $pattern ) {
			if ( preg_match( $pattern, $content, $matches ) ) {
				$issues[] = array(
					'type'        => 'unescaped_output',
					'severity'    => 'high',
					'description' => __( 'Unescaped output detected - potential XSS vulnerability', 'wpshadow' ),
					'pattern'     => $matches[0],
				);
			}
		}

		// Check for direct $_POST access without sanitization.
		if ( preg_match_all( self::SECURITY_PATTERNS['direct_access'][0], $content, $matches ) ) {
			foreach ( $matches[0] as $match ) {
				$issues[] = array(
					'type'        => 'unsanitized_input',
					'severity'    => 'high',
					'description' => __( 'Direct superglobal access without sanitization', 'wpshadow' ),
					'pattern'     => substr( $match, 0, 50 ),
				);
			}
		}

		// Check for form submissions without nonce.
		if ( preg_match( '/\$_POST/', $content ) && ! preg_match( '/wp_verify_nonce/', $content ) ) {
			$issues[] = array(
				'type'        => 'missing_nonce',
				'severity'    => 'medium',
				'description' => __( 'Form processing without nonce verification', 'wpshadow' ),
			);
		}

		// Check for missing capability checks.
		if ( preg_match( '/add_(?:menu|submenu)_page/', $content ) && ! preg_match( '/current_user_can/', $content ) ) {
			$issues[] = array(
				'type'        => 'missing_capability_check',
				'severity'    => 'medium',
				'description' => __( 'Admin page without capability check', 'wpshadow' ),
			);
		}

		return $issues;
	}

	/**
	 * Build finding array from analysis.
	 *
	 * @since  1.6028.1725
	 * @param  array $analysis Analysis results.
	 * @return array Finding array.
	 */
	private static function build_finding( array $analysis ): array {
		$file_count = count( $analysis['vulnerable_files'] );
		$severity   = 'medium';
		$threat     = 55;

		// Calculate severity based on issue types.
		$has_high_severity = false;
		foreach ( $analysis['vulnerable_files'] as $file ) {
			foreach ( $file['issues'] as $issue ) {
				if ( 'high' === $issue['severity'] ) {
					$has_high_severity = true;
					break 2;
				}
			}
		}

		if ( $has_high_severity ) {
			$severity = 'high';
			$threat   = 65;
		}

		$description = sprintf(
			/* translators: 1: file count, 2: issue count */
			_n(
				'Found %2$d security issue in %1$d plugin admin file',
				'Found %2$d security issues in %1$d plugin admin files',
				$file_count,
				'wpshadow'
			),
			$file_count,
			$analysis['issue_count']
		);

		$recommendations = array(
			__( 'Always escape output using esc_html(), esc_attr(), esc_url()', 'wpshadow' ),
			__( 'Sanitize all input using sanitize_text_field(), sanitize_email(), etc.', 'wpshadow' ),
			__( 'Verify nonces for all form submissions', 'wpshadow' ),
			__( 'Check user capabilities before processing admin actions', 'wpshadow' ),
			__( 'Follow WordPress Security Best Practices', 'wpshadow' ),
		);

		// Group issues by type.
		$issue_types = array();
		foreach ( $analysis['vulnerable_files'] as $file ) {
			foreach ( $file['issues'] as $issue ) {
				if ( ! isset( $issue_types[ $issue['type'] ] ) ) {
					$issue_types[ $issue['type'] ] = 0;
				}
				++$issue_types[ $issue['type'] ];
			}
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/plugin-admin-page-security',
			'family'      => self::$family,
			'meta'        => array(
				'total_scanned'    => $analysis['total_scanned'],
				'vulnerable_files' => $file_count,
				'issue_count'      => $analysis['issue_count'],
				'issue_types'      => $issue_types,
			),
			'details'     => array(
				'vulnerable_files' => $analysis['vulnerable_files'],
				'recommendations'  => $recommendations,
				'security_note'    => __( 'XSS and CSRF vulnerabilities in admin pages can lead to privilege escalation and site compromise', 'wpshadow' ),
			),
		);
	}
}
