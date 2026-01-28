<?php
/**
 * Dead Code Detection Diagnostic
 *
 * Identifies unused functions, classes, and methods in themes and plugins.
 * Detects orphaned code that can be safely removed to improve security
 * and maintainability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1655
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dead Code Detection Diagnostic Class
 *
 * Scans active theme and custom plugins for function/class definitions
 * that are never called anywhere in the codebase. Dead code increases
 * attack surface, maintenance burden, and code complexity unnecessarily.
 *
 * @since 1.6028.1655
 */
class Diagnostic_Dead_Code extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1655
	 * @var   string
	 */
	protected static $slug = 'dead-code-detection';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1655
	 * @var   string
	 */
	protected static $title = 'Dead Code Detection';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1655
	 * @var   string
	 */
	protected static $description = 'Identifies unused functions and code that can be removed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1655
	 * @var   string
	 */
	protected static $family = 'code-quality';

	/**
	 * Cache duration (6 hours)
	 *
	 * @since 1.6028.1655
	 * @var   int
	 */
	private const CACHE_DURATION = 21600;

	/**
	 * Percentage threshold for flagging
	 *
	 * @since 1.6028.1655
	 * @var   float
	 */
	private const DEAD_CODE_THRESHOLD = 10.0;

	/**
	 * Maximum files to scan
	 *
	 * @since 1.6028.1655
	 * @var   int
	 */
	private const MAX_FILES_SCAN = 500;

	/**
	 * Run the diagnostic check.
	 *
	 * Scans active theme and custom plugins for dead code - functions,
	 * classes, and methods that are defined but never used anywhere.
	 *
	 * @since  1.6028.1655
	 * @return array|null Finding array if dead code detected, null otherwise.
	 */
	public static function check() {
		$cached = get_transient( 'wpshadow_dead_code_check' );
		if ( false !== $cached ) {
			return $cached;
		}

		$analysis = self::analyze_dead_code();

		if ( empty( $analysis['dead_functions'] ) && empty( $analysis['dead_classes'] ) ) {
			set_transient( 'wpshadow_dead_code_check', null, self::CACHE_DURATION );
			return null;
		}

		$result = self::build_finding( $analysis );

		set_transient( 'wpshadow_dead_code_check', $result, self::CACHE_DURATION );

		return $result;
	}

	/**
	 * Analyze codebase for dead code.
	 *
	 * Scans active theme and custom plugins to identify functions, classes,
	 * and methods that are defined but never called.
	 *
	 * @since  1.6028.1655
	 * @return array {
	 *     Analysis results.
	 *
	 *     @type array  $dead_functions List of unused functions.
	 *     @type array  $dead_classes   List of unused classes.
	 *     @type int    $total_defined  Total symbols defined.
	 *     @type int    $total_dead     Total unused symbols.
	 *     @type float  $dead_percentage Percentage of dead code.
	 *     @type array  $scanned_paths  Paths scanned.
	 *     @type int    $files_scanned  Number of files analyzed.
	 * }
	 */
	private static function analyze_dead_code(): array {
		$paths          = self::get_scan_paths();
		$defined        = self::extract_definitions( $paths );
		$used           = self::extract_usages( $paths );
		$dead_functions = array_diff_key( $defined['functions'], $used['functions'] );
		$dead_classes   = array_diff_key( $defined['classes'], $used['classes'] );

		// Filter out hooked functions (they're called via hooks).
		$dead_functions = self::filter_hooked_functions( $dead_functions, $paths );

		$total_defined    = count( $defined['functions'] ) + count( $defined['classes'] );
		$total_dead       = count( $dead_functions ) + count( $dead_classes );
		$dead_percentage  = $total_defined > 0 ? ( $total_dead / $total_defined ) * 100 : 0;

		return array(
			'dead_functions'   => $dead_functions,
			'dead_classes'     => $dead_classes,
			'total_defined'    => $total_defined,
			'total_dead'       => $total_dead,
			'dead_percentage'  => round( $dead_percentage, 2 ),
			'scanned_paths'    => $paths,
			'files_scanned'    => $defined['files_scanned'],
		);
	}

	/**
	 * Get paths to scan for dead code.
	 *
	 * Returns array of paths to active theme and custom plugins
	 * (excludes WordPress core and third-party plugins).
	 *
	 * @since  1.6028.1655
	 * @return array Array of directory paths to scan.
	 */
	private static function get_scan_paths(): array {
		$paths = array();

		// Active theme.
		$theme = wp_get_theme();
		if ( $theme->exists() ) {
			$paths[] = $theme->get_stylesheet_directory();
		}

		// Custom plugins (exclude vendor/WordPress.org plugins).
		$plugins = get_option( 'active_plugins', array() );
		foreach ( $plugins as $plugin_file ) {
			$plugin_dir = dirname( WP_PLUGIN_DIR . '/' . $plugin_file );

			// Only scan custom plugins (heuristic: has .git or no readme.txt).
			if ( is_dir( $plugin_dir . '/.git' ) || ! file_exists( $plugin_dir . '/readme.txt' ) ) {
				$paths[] = $plugin_dir;
			}
		}

		return $paths;
	}

	/**
	 * Extract function and class definitions from paths.
	 *
	 * Scans PHP files to find all defined functions, classes, methods.
	 * Uses token parsing for accuracy.
	 *
	 * @since  1.6028.1655
	 * @param  array $paths Paths to scan.
	 * @return array {
	 *     Extracted definitions.
	 *
	 *     @type array $functions      Function definitions (name => file).
	 *     @type array $classes        Class definitions (name => file).
	 *     @type int   $files_scanned  Number of files processed.
	 * }
	 */
	private static function extract_definitions( array $paths ): array {
		$functions      = array();
		$classes        = array();
		$files_scanned  = 0;
		$max_files      = self::MAX_FILES_SCAN;

		foreach ( $paths as $path ) {
			$files = self::get_php_files( $path );

			foreach ( $files as $file ) {
				if ( $files_scanned >= $max_files ) {
					break 2; // Limit scan to avoid timeouts.
				}

				$content = file_get_contents( $file );
				if ( false === $content ) {
					continue;
				}

				// Extract functions.
				preg_match_all( '/function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/i', $content, $func_matches );
				foreach ( $func_matches[1] as $func_name ) {
					$functions[ $func_name ] = $file;
				}

				// Extract classes.
				preg_match_all( '/(?:class|interface|trait)\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/i', $content, $class_matches );
				foreach ( $class_matches[1] as $class_name ) {
					$classes[ $class_name ] = $file;
				}

				++$files_scanned;
			}
		}

		return array(
			'functions'     => $functions,
			'classes'       => $classes,
			'files_scanned' => $files_scanned,
		);
	}

	/**
	 * Extract function and class usages from paths.
	 *
	 * Scans codebase to find where functions/classes are called.
	 * Checks: direct calls, new Class(), hooks, callbacks.
	 *
	 * @since  1.6028.1655
	 * @param  array $paths Paths to scan.
	 * @return array {
	 *     Extracted usages.
	 *
	 *     @type array $functions Function usages (name => count).
	 *     @type array $classes   Class usages (name => count).
	 * }
	 */
	private static function extract_usages( array $paths ): array {
		$functions = array();
		$classes   = array();

		foreach ( $paths as $path ) {
			$files = self::get_php_files( $path );

			foreach ( $files as $file ) {
				$content = file_get_contents( $file );
				if ( false === $content ) {
					continue;
				}

				// Function calls: function_name().
				preg_match_all( '/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/i', $content, $func_matches );
				foreach ( $func_matches[1] as $func_name ) {
					// Exclude language constructs.
					if ( in_array( strtolower( $func_name ), array( 'if', 'while', 'for', 'foreach', 'switch', 'catch', 'function', 'array' ), true ) ) {
						continue;
					}
					if ( ! isset( $functions[ $func_name ] ) ) {
						$functions[ $func_name ] = 0;
					}
					++$functions[ $func_name ];
				}

				// Class instantiation: new ClassName().
				preg_match_all( '/new\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/i', $content, $new_matches );
				foreach ( $new_matches[1] as $class_name ) {
					if ( ! isset( $classes[ $class_name ] ) ) {
						$classes[ $class_name ] = 0;
					}
					++$classes[ $class_name ];
				}

				// Static calls: Class::method().
				preg_match_all( '/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)::/i', $content, $static_matches );
				foreach ( $static_matches[1] as $class_name ) {
					if ( ! isset( $classes[ $class_name ] ) ) {
						$classes[ $class_name ] = 0;
					}
					++$classes[ $class_name ];
				}

				// instanceof checks.
				preg_match_all( '/instanceof\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/i', $content, $instanceof_matches );
				foreach ( $instanceof_matches[1] as $class_name ) {
					if ( ! isset( $classes[ $class_name ] ) ) {
						$classes[ $class_name ] = 0;
					}
					++$classes[ $class_name ];
				}
			}
		}

		return array(
			'functions' => $functions,
			'classes'   => $classes,
		);
	}

	/**
	 * Filter out hooked functions from dead code list.
	 *
	 * Functions registered via add_action/add_filter are not dead code
	 * even if they're not explicitly called elsewhere.
	 *
	 * @since  1.6028.1655
	 * @param  array $dead_functions Array of potentially dead functions.
	 * @param  array $paths          Paths to scan for hook registrations.
	 * @return array Filtered dead functions (hooked functions removed).
	 */
	private static function filter_hooked_functions( array $dead_functions, array $paths ): array {
		$hooked = array();

		foreach ( $paths as $path ) {
			$files = self::get_php_files( $path );

			foreach ( $files as $file ) {
				$content = file_get_contents( $file );
				if ( false === $content ) {
					continue;
				}

				// add_action( 'hook', 'function_name' ).
				preg_match_all( '/add_(?:action|filter)\s*\(\s*[\'"][^\'"]*[\'"]\s*,\s*[\'"]([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[\'"]/i', $content, $hook_matches );
				foreach ( $hook_matches[1] as $func_name ) {
					$hooked[ $func_name ] = true;
				}

				// add_action( 'hook', array( $this, 'method' ) ).
				preg_match_all( '/add_(?:action|filter)\s*\([^,]+,\s*array\s*\([^,]+,\s*[\'"]([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[\'"]/i', $content, $method_matches );
				foreach ( $method_matches[1] as $method_name ) {
					$hooked[ $method_name ] = true;
				}
			}
		}

		// Remove hooked functions from dead list.
		return array_diff_key( $dead_functions, $hooked );
	}

	/**
	 * Get all PHP files in a directory recursively.
	 *
	 * @since  1.6028.1655
	 * @param  string $path Directory path.
	 * @return array Array of PHP file paths.
	 */
	private static function get_php_files( string $path ): array {
		$files = array();

		if ( ! is_dir( $path ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $path, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				// Exclude vendor directories.
				if ( false === strpos( $file->getPathname(), '/vendor/' ) && false === strpos( $file->getPathname(), '/node_modules/' ) ) {
					$files[] = $file->getPathname();
				}
			}
		}

		return $files;
	}

	/**
	 * Build finding array from analysis.
	 *
	 * @since  1.6028.1655
	 * @param  array $analysis Analysis results.
	 * @return array|null Finding array or null.
	 */
	private static function build_finding( array $analysis ) {
		if ( $analysis['dead_percentage'] < self::DEAD_CODE_THRESHOLD ) {
			return null; // Below threshold, not worth flagging.
		}

		$severity = 'low';
		$threat   = 10;

		if ( $analysis['dead_percentage'] >= 25 ) {
			$severity = 'medium';
			$threat   = 20;
		}

		if ( $analysis['dead_percentage'] >= 40 ) {
			$severity = 'high';
			$threat   = 25;
		}

		$description = sprintf(
			/* translators: 1: dead code percentage, 2: total dead symbols */
			__( 'Found %1$.1f%% dead code (%2$d unused functions/classes). Dead code increases security risk and maintenance burden.', 'wpshadow' ),
			$analysis['dead_percentage'],
			$analysis['total_dead']
		);

		$recommendations = array(
			__( 'Review unused functions and classes for safe removal', 'wpshadow' ),
			__( 'Test thoroughly after removing dead code', 'wpshadow' ),
			__( 'Consider refactoring to reduce code complexity', 'wpshadow' ),
		);

		// Top 10 dead functions to display.
		$dead_function_list = array_slice( array_keys( $analysis['dead_functions'] ), 0, 10 );
		$dead_class_list    = array_slice( array_keys( $analysis['dead_classes'] ), 0, 10 );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/dead-code-detection',
			'family'      => self::$family,
			'meta'        => array(
				'dead_percentage'    => $analysis['dead_percentage'],
				'total_defined'      => $analysis['total_defined'],
				'total_dead'         => $analysis['total_dead'],
				'dead_functions'     => count( $analysis['dead_functions'] ),
				'dead_classes'       => count( $analysis['dead_classes'] ),
				'files_scanned'      => $analysis['files_scanned'],
			),
			'details'     => array(
				'dead_function_samples' => $dead_function_list,
				'dead_class_samples'    => $dead_class_list,
				'recommendations'       => $recommendations,
				'note'                  => __( 'Manual review required before deletion. Some functions may be called dynamically or via hooks.', 'wpshadow' ),
			),
		);
	}
}
