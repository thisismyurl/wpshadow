<?php
/**
 * Currency Symbol Hardcoded Diagnostic
 *
 * Detects hardcoded USD currency symbol ($), which confuses non-US customers
 * and breaks multi-currency sites.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\I18n
 * @since      1.6028.2140
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currency Symbol Hardcoded Diagnostic Class
 *
 * Searches for hardcoded "$" symbols in price displays instead of using
 * proper currency functions or settings.
 *
 * @since 1.6028.2140
 */
class Diagnostic_Currency_Symbol_Hardcoded extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'currency-symbol-hardcoded';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Currency Symbol Hardcoded as $';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects hardcoded $ currency symbols breaking internationalization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'i18n';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.2140
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cache_key = 'wpshadow_currency_symbol_hardcoded_check';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Scan custom theme and plugin files.
		$files      = self::get_custom_php_files();
		$violations = array();

		foreach ( $files as $file ) {
			$issues = self::scan_file_for_currency_issues( $file );
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
			$result = null; // All currency symbols properly handled.
		} else {
			$severity     = $total_violations > 10 ? 'medium' : 'low';
			$threat_level = min( 40, 15 + ( $total_violations * 2 ) );

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of hardcoded currency symbols */
					_n(
						'Found %d hardcoded $ currency symbol confusing international customers',
						'Found %d hardcoded $ currency symbols confusing international customers',
						$total_violations,
						'wpshadow'
					),
					$total_violations
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/i18n-currency-symbol',
				'family'       => self::$family,
				'meta'         => array(
					'total_violations' => $total_violations,
					'files_affected'   => count( $violations ),
					'woocommerce_active' => class_exists( 'WooCommerce' ),
				),
				'details'      => array(
					'violations' => self::format_violations( $violations ),
				),
				'recommendations' => array(
					__( 'Use get_woocommerce_currency_symbol() for WooCommerce sites', 'wpshadow' ),
					__( 'Store currency in settings/options for dynamic display', 'wpshadow' ),
					__( 'Use multi-currency plugins for international support', 'wpshadow' ),
					__( 'Replace hardcoded "$" with proper currency functions', 'wpshadow' ),
					__( 'Test display with different currency settings', 'wpshadow' ),
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
	 * @since  1.6028.2140
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
	 * @since  1.6028.2140
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
	 * Scan file for currency formatting issues.
	 *
	 * @since  1.6028.2140
	 * @param  string $file File path.
	 * @return array Array of issues found.
	 */
	private static function scan_file_for_currency_issues( $file ) {
		$issues = array();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $file );
		if ( false === $content ) {
			return $issues;
		}

		$lines = explode( "\n", $content );

		foreach ( $lines as $line_num => $line ) {
			// Look for hardcoded $ with numbers (price patterns).
			if ( preg_match( '/["\']?\$\s*\d+/', $line ) || preg_match( '/["\']?\$\{/', $line ) ) {
				// Exclude variable references like $price or ${variable}.
				if ( ! preg_match( '/\$[a-zA-Z_]/', $line ) ) {
					$issues[] = array(
						'line'        => $line_num + 1,
						'description' => 'Hardcoded $ currency symbol with price',
						'code'        => trim( $line ),
					);
				}
			}

			// Look for common price display patterns.
			if ( preg_match( '/(echo|print|return).*["\'].*\$.*\d/', $line ) ) {
				$issues[] = array(
					'line'        => $line_num + 1,
					'description' => 'Hardcoded currency in output',
					'code'        => trim( $line ),
				);
			}

			// Look for "USD" or "US$" hardcoded.
			if ( preg_match( '/["\' ](USD|US\$)["\' ]/', $line ) ) {
				$issues[] = array(
					'line'        => $line_num + 1,
					'description' => 'Hardcoded USD currency code',
					'code'        => trim( $line ),
				);
			}
		}

		return $issues;
	}

	/**
	 * Format violations for display.
	 *
	 * @since  1.6028.2140
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
