<?php
/**
 * PHP 8+ Incompatible Code Patterns Diagnostic
 *
 * Detects code patterns incompatible with PHP 8+, blocking upgrade path
 * for performance and security improvements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\CodeQuality
 * @since      1.6028.2125
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PHP 8+ Incompatible Code Patterns Diagnostic Class
 *
 * Scans custom code for patterns that break in PHP 8+, helping identify
 * compatibility issues before upgrading PHP version.
 *
 * @since 1.6028.2125
 */
class Diagnostic_PHP8_Incompatible_Patterns extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php8-incompatible-patterns';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP 8+ Incompatible Code Patterns';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects code patterns incompatible with PHP 8+';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.2125
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cache_key = 'wpshadow_php8_incompatible_patterns_check';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Scan custom theme and plugin files.
		$files          = self::get_custom_php_files();
		$incompatibilities = array();

		foreach ( $files as $file ) {
			$issues = self::scan_file_for_php8_issues( $file );
			if ( ! empty( $issues ) ) {
				$incompatibilities[ $file ] = $issues;
			}
		}

		// Count total issues.
		$total_issues = 0;
		foreach ( $incompatibilities as $issues ) {
			$total_issues += count( $issues );
		}

		// Determine if there's a finding.
		if ( $total_issues === 0 ) {
			$result = null; // PHP 8+ compatible.
		} else {
			$severity     = $total_issues > 10 ? 'high' : ( $total_issues > 5 ? 'medium' : 'low' );
			$threat_level = min( 75, 30 + ( $total_issues * 3 ) );

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of incompatible patterns */
					_n(
						'Found %d PHP 8+ incompatible code pattern blocking upgrade path',
						'Found %d PHP 8+ incompatible code patterns blocking upgrade path',
						$total_issues,
						'wpshadow'
					),
					$total_issues
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/code-quality-php8-compatibility',
				'family'       => self::$family,
				'meta'         => array(
					'total_issues'       => $total_issues,
					'files_affected'     => count( $incompatibilities ),
					'php_version'        => PHP_VERSION,
					'php8_compatible'    => version_compare( PHP_VERSION, '8.0', '>=' ),
				),
				'details'      => array(
					'incompatibilities' => self::format_incompatibilities( $incompatibilities ),
				),
				'recommendations' => array(
					__( 'Update code to use PHP 8+ compatible syntax', 'wpshadow' ),
					__( 'Replace deprecated functions with modern alternatives', 'wpshadow' ),
					__( 'Fix constructor methods using old PHP 4 syntax', 'wpshadow' ),
					__( 'Update type declarations to strict types where possible', 'wpshadow' ),
					__( 'Test thoroughly on PHP 8+ environment before upgrading', 'wpshadow' ),
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
	 * @since  1.6028.2125
	 * @return array Array of file paths.
	 */
	private static function get_custom_php_files() {
		$files = array();

		// Scan active theme.
		$theme_dir = get_stylesheet_directory();
		$theme_files = self::scan_directory_for_php( $theme_dir );
		$files       = array_merge( $files, array_slice( $theme_files, 0, 30 ) );

		// Scan custom plugins (skip vendor directories).
		$plugins_dir = WP_PLUGIN_DIR;
		$plugins     = get_option( 'active_plugins', array() );

		foreach ( $plugins as $plugin ) {
			$plugin_dir = dirname( $plugins_dir . '/' . $plugin );
			if ( strpos( $plugin_dir, 'wpshadow' ) !== false || strpos( $plugin_dir, 'vendor' ) !== false ) {
				continue; // Skip our own plugins and vendor.
			}
			$plugin_files = self::scan_directory_for_php( $plugin_dir );
			$files        = array_merge( $files, array_slice( $plugin_files, 0, 10 ) );
		}

		return array_slice( $files, 0, 50 ); // Limit total.
	}

	/**
	 * Scan directory for PHP files.
	 *
	 * @since  1.6028.2125
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
				// Skip vendor, node_modules, tests.
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
	 * Scan file for PHP 8 incompatibilities.
	 *
	 * @since  1.6028.2125
	 * @param  string $file File path.
	 * @return array Array of issues found.
	 */
	private static function scan_file_for_php8_issues( $file ) {
		$issues = array();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $file );
		if ( false === $content ) {
			return $issues;
		}

		$lines = explode( "\n", $content );

		// Pattern 1: PHP 4 constructors (function ClassName()).
		if ( preg_match( '/class\s+(\w+)/i', $content, $class_match ) ) {
			$class_name = $class_match[1];
			if ( preg_match( '/function\s+' . preg_quote( $class_name, '/' ) . '\s*\(/i', $content, $match, PREG_OFFSET_CAPTURE ) ) {
				$line         = substr_count( substr( $content, 0, $match[0][1] ), "\n" ) + 1;
				$issues[]     = array(
					'type'        => 'php4_constructor',
					'line'        => $line,
					'description' => sprintf( 'PHP 4 style constructor function %s()', $class_name ),
				);
			}
		}

		// Pattern 2: create_function() (removed in PHP 8).
		if ( strpos( $content, 'create_function' ) !== false ) {
			foreach ( $lines as $line_num => $line ) {
				if ( strpos( $line, 'create_function' ) !== false ) {
					$issues[] = array(
						'type'        => 'create_function',
						'line'        => $line_num + 1,
						'description' => 'create_function() removed in PHP 8',
					);
				}
			}
		}

		// Pattern 3: each() (removed in PHP 8).
		if ( strpos( $content, 'each(' ) !== false ) {
			foreach ( $lines as $line_num => $line ) {
				if ( preg_match( '/\beach\s*\(/', $line ) ) {
					$issues[] = array(
						'type'        => 'each_function',
						'line'        => $line_num + 1,
						'description' => 'each() removed in PHP 8',
					);
				}
			}
		}

		// Pattern 4: Unparenthesized `new` in expressions.
		if ( preg_match( '/new\s+\w+[^(;\s]/', $content ) ) {
			foreach ( $lines as $line_num => $line ) {
				if ( preg_match( '/new\s+\w+\s*->/i', $line ) ) {
					$issues[] = array(
						'type'        => 'new_without_parentheses',
						'line'        => $line_num + 1,
						'description' => 'new without parentheses in chained expression',
					);
				}
			}
		}

		// Pattern 5: Deprecated parameter order (implode, join).
		foreach ( $lines as $line_num => $line ) {
			if ( preg_match( '/\b(implode|join)\s*\(\s*\$/', $line ) ) {
				$issues[] = array(
					'type'        => 'deprecated_implode_order',
					'line'        => $line_num + 1,
					'description' => 'Possible deprecated parameter order in implode/join',
				);
			}
		}

		return $issues;
	}

	/**
	 * Format incompatibilities for display.
	 *
	 * @since  1.6028.2125
	 * @param  array $incompatibilities Incompatibilities array.
	 * @return array Formatted array.
	 */
	private static function format_incompatibilities( $incompatibilities ) {
		$formatted = array();
		$count     = 0;

		foreach ( $incompatibilities as $file => $issues ) {
			foreach ( $issues as $issue ) {
				$formatted[] = array(
					'file'        => basename( $file ),
					'line'        => $issue['line'],
					'type'        => $issue['type'],
					'description' => $issue['description'],
				);
				++$count;
				if ( $count >= 20 ) {
					break 2;
				}
			}
		}

		return $formatted;
	}
}
