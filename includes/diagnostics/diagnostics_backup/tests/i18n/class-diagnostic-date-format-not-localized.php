<?php
/**
 * Date Format Not Localized Diagnostic
 *
 * Detects hardcoded date formats (MM/DD/YYYY) assuming US conventions,
 * confusing international users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\I18n
 * @since      1.6028.2135
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date Format Not Localized Diagnostic Class
 *
 * Searches for hardcoded date() calls instead of date_i18n(),
 * which causes dates to display incorrectly for international users.
 *
 * @since 1.6028.2135
 */
class Diagnostic_Date_Format_Not_Localized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'date-format-not-localized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Date Format Not Localized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects hardcoded date formats instead of localized functions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'i18n';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.2135
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cache_key = 'wpshadow_date_format_not_localized_check';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Scan custom theme and plugin files.
		$files      = self::get_custom_php_files();
		$violations = array();

		foreach ( $files as $file ) {
			$issues = self::scan_file_for_date_issues( $file );
			if ( ! empty( $issues ) ) {
				$violations[ $file ] = $issues;
			}
		}

		// Count total violations.
		$total_violations = 0;
		foreach ( $violations as $issues ) {
			$total_violations += count( $issues );
		}

		// Determine if there's an issue.
		if ( $total_violations === 0 ) {
			$result = null; // All dates properly localized.
		} else {
			$severity     = $total_violations > 10 ? 'medium' : 'low';
			$threat_level = min( 40, 15 + ( $total_violations * 2 ) );

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of hardcoded date formats */
					_n(
						'Found %d hardcoded date format confusing international users',
						'Found %d hardcoded date formats confusing international users',
						$total_violations,
						'wpshadow'
					),
					$total_violations
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/i18n-date-format',
				'family'       => self::$family,
				'meta'         => array(
					'total_violations' => $total_violations,
					'files_affected'   => count( $violations ),
					'date_format'      => get_option( 'date_format' ),
					'time_format'      => get_option( 'time_format' ),
				),
				'details'      => array(
					'violations' => self::format_violations( $violations ),
				),
				'recommendations' => array(
					__( 'Use date_i18n() instead of date() for localization support', 'wpshadow' ),
					__( 'Use get_option("date_format") to respect user preferences', 'wpshadow' ),
					__( 'Use get_option("time_format") for time display', 'wpshadow' ),
					__( 'Avoid hardcoded date formats like "m/d/Y" or "MM/DD/YYYY"', 'wpshadow' ),
					__( 'Test date display with different language settings', 'wpshadow' ),
				),
			);
		}

		// Cache for 6 hours.
		set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Get custom PHP files to scan.
	 *
	 * @since  1.6028.2135
	 * @return array Array of file paths.
	 */
	private static function get_custom_php_files() {
		$files = array();

		// Scan active theme.
		$theme_dir   = get_stylesheet_directory();
		$theme_files = self::scan_directory_for_php( $theme_dir );
		$files       = array_merge( $files, array_slice( $theme_files, 0, 30 ) );

		// Scan custom plugins.
		$plugins_dir = WP_PLUGIN_DIR;
		$plugins     = get_option( 'active_plugins', array() );

		foreach ( $plugins as $plugin ) {
			$plugin_dir = dirname( $plugins_dir . '/' . $plugin );
			if ( strpos( $plugin_dir, 'wpshadow' ) !== false || strpos( $plugin_dir, 'vendor' ) !== false ) {
				continue;
			}
			$plugin_files = self::scan_directory_for_php( $plugin_dir );
			$files        = array_merge( $files, array_slice( $plugin_files, 0, 10 ) );
		}

		return array_slice( $files, 0, 50 );
	}

	/**
	 * Scan directory for PHP files.
	 *
	 * @since  1.6028.2135
	 * @param  string $dir Directory to scan.
	 * @return array Array of PHP file paths.
	 */
	private static function scan_directory_for_php( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return array();
		}

		$files    = array();
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$path = $file->getPathname();
				if ( strpos( $path, '/vendor/' ) === false &&
					strpos( $path, '/node_modules/' ) === false &&
					strpos( $path, '/tests/' ) === false ) {
					$files[] = $path;
				}
			}
		}

		return $files;
	}

	/**
	 * Scan file for date formatting issues.
	 *
	 * @since  1.6028.2135
	 * @param  string $file File path.
	 * @return array Array of issues found.
	 */
	private static function scan_file_for_date_issues( $file ) {
		$issues = array();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $file );
		if ( false === $content ) {
			return $issues;
		}

		$lines = explode( "\n", $content );

		foreach ( $lines as $line_num => $line ) {
			// Look for date() calls (but not date_i18n).
			if ( preg_match( '/\bdate\s*\([^)]+\)/i', $line ) && strpos( $line, 'date_i18n' ) === false ) {
				$issues[] = array(
					'line'        => $line_num + 1,
					'description' => 'Using date() instead of date_i18n()',
					'code'        => trim( $line ),
				);
			}

			// Look for hardcoded date format strings.
			if ( preg_match( '/["\'](\d+\/\d+\/\d+|m\/d\/Y|Y-m-d|d\.m\.Y)["\']/', $line ) ) {
				$issues[] = array(
					'line'        => $line_num + 1,
					'description' => 'Hardcoded date format string',
					'code'        => trim( $line ),
				);
			}
		}

		return $issues;
	}

	/**
	 * Format violations for display.
	 *
	 * @since  1.6028.2135
	 * @param  array $violations Violations array.
	 * @return array Formatted array.
	 */
	private static function format_violations( $violations ) {
		$formatted = array();
		$count     = 0;

		foreach ( $violations as $file => $issues ) {
			foreach ( $issues as $issue ) {
				$formatted[] = array(
					'file'        => basename( $file ),
					'line'        => $issue['line'],
					'description' => $issue['description'],
					'code'        => substr( $issue['code'], 0, 80 ),
				);
				++$count;
				if ( $count >= 15 ) {
					break 2;
				}
			}
		}

		return $formatted;
	}
}
