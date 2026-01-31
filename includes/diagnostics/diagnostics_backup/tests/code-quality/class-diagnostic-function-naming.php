<?php
/**
 * Function Naming Convention Audit Diagnostic
 *
 * Validates that custom plugin and theme functions follow WordPress snake_case naming convention.
 * Uses PHP tokenizer to extract and analyze function names.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1735
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function Naming Convention Diagnostic Class
 *
 * Parses PHP files to extract function names and validates they follow
 * WordPress snake_case naming convention.
 *
 * @since 1.6028.1735
 */
class Diagnostic_Function_Naming extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1735
	 * @var   string
	 */
	protected static $slug = 'function-naming-convention';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1735
	 * @var   string
	 */
	protected static $title = 'Function Naming Convention Audit';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1735
	 * @var   string
	 */
	protected static $description = 'Validates custom functions use WordPress snake_case naming convention';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1735
	 * @var   string
	 */
	protected static $family = 'code-quality';

	/**
	 * Cache duration in seconds (6 hours)
	 *
	 * @since 1.6028.1735
	 */
	private const CACHE_DURATION = 21600;

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6028.1735
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Check cache first
		$cache_key = 'wpshadow_diagnostic_function_naming';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			if ( null === $cached ) {
				return null;
			}
			return self::build_finding( $cached );
		}

		// Analyze function naming
		$analysis = self::analyze_function_naming();

		// Cache result
		set_transient( $cache_key, $analysis, self::CACHE_DURATION );

		if ( null === $analysis ) {
			return null;
		}

		return self::build_finding( $analysis );
	}

	/**
	 * Analyze function naming conventions
	 *
	 * @since  1.6028.1735
	 * @return array|null Analysis results or null if compliant.
	 */
	private static function analyze_function_naming() {
		// Get custom theme and plugin files
		$files_to_scan = self::get_custom_files();

		if ( empty( $files_to_scan ) ) {
			return null;
		}

		$total_functions = 0;
		$violations      = array();

		foreach ( $files_to_scan as $file_path ) {
			$functions = self::extract_functions_from_file( $file_path );

			foreach ( $functions as $function_data ) {
				++$total_functions;

				if ( ! self::is_valid_snake_case( $function_data['name'] ) ) {
					$violations[] = array(
						'file'             => str_replace( ABSPATH, '', $file_path ),
						'function'         => $function_data['name'],
						'line'             => $function_data['line'],
						'suggested_name'   => self::convert_to_snake_case( $function_data['name'] ),
						'violation_type'   => self::get_violation_type( $function_data['name'] ),
					);
				}
			}
		}

		// No violations found
		if ( empty( $violations ) ) {
			return null;
		}

		// Calculate compliance
		$compliance_percentage = $total_functions > 0 
			? ( ( $total_functions - count( $violations ) ) / $total_functions ) * 100 
			: 100;

		// Group by violation type
		$by_type = array();
		foreach ( $violations as $violation ) {
			$type = $violation['violation_type'];
			if ( ! isset( $by_type[ $type ] ) ) {
				$by_type[ $type ] = 0;
			}
			++$by_type[ $type ];
		}

		return array(
			'total_functions'       => $total_functions,
			'total_violations'      => count( $violations ),
			'compliance_percentage' => round( $compliance_percentage, 1 ),
			'violations'            => array_slice( $violations, 0, 20 ), // Top 20
			'violations_by_type'    => $by_type,
		);
	}

	/**
	 * Get custom theme and plugin PHP files
	 *
	 * @since  1.6028.1735
	 * @return array Array of file paths.
	 */
	private static function get_custom_files(): array {
		$files = array();

		// Get active theme files
		$theme_dir = get_stylesheet_directory();
		if ( is_dir( $theme_dir ) ) {
			$theme_files = self::scan_directory_for_php( $theme_dir );
			$files = array_merge( $files, $theme_files );
		}

		// Get custom plugin files
		$plugins = get_plugins();
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
			if ( is_dir( $plugin_dir ) ) {
				$plugin_files = self::scan_directory_for_php( $plugin_dir );
				$files = array_merge( $files, $plugin_files );
			}
		}

		return array_unique( $files );
	}

	/**
	 * Scan directory for PHP files
	 *
	 * @since  1.6028.1735
	 * @param  string $directory Directory path.
	 * @return array Array of PHP file paths.
	 */
	private static function scan_directory_for_php( string $directory ): array {
		$files = array();

		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( ! $file->isFile() || 'php' !== $file->getExtension() ) {
					continue;
				}

				$file_path = $file->getPathname();

				// Skip vendor, node_modules, tests
				if ( preg_match( '#/(vendor|node_modules|tests|test)/#', $file_path ) ) {
					continue;
				}

				// Limit to first 30 files per directory
				if ( count( $files ) >= 30 ) {
					break;
				}

				$files[] = $file_path;
			}
		} catch ( \Exception $e ) {
			return array();
		}

		return $files;
	}

	/**
	 * Extract functions from PHP file using tokenizer
	 *
	 * @since  1.6028.1735
	 * @param  string $file_path File path.
	 * @return array Array of function data.
	 */
	private static function extract_functions_from_file( string $file_path ): array {
		$content = @file_get_contents( $file_path );
		if ( false === $content ) {
			return array();
		}

		$tokens = @token_get_all( $content );
		if ( ! is_array( $tokens ) ) {
			return array();
		}

		$functions  = array();
		$in_class   = false;
		$class_depth = 0;

		for ( $i = 0; $i < count( $tokens ); $i++ ) {
			$token = $tokens[ $i ];

			// Track class scope (skip methods)
			if ( is_array( $token ) && T_CLASS === $token[0] ) {
				$in_class = true;
				continue;
			}

			// Track braces for class scope
			if ( '{' === $token ) {
				if ( $in_class ) {
					++$class_depth;
				}
				continue;
			}

			if ( '}' === $token && $class_depth > 0 ) {
				--$class_depth;
				if ( 0 === $class_depth ) {
					$in_class = false;
				}
				continue;
			}

			// Look for function keyword (outside classes)
			if ( is_array( $token ) && T_FUNCTION === $token[0] && ! $in_class ) {
				// Get next meaningful token (function name)
				for ( $j = $i + 1; $j < count( $tokens ); $j++ ) {
					$next_token = $tokens[ $j ];

					if ( is_array( $next_token ) && T_STRING === $next_token[0] ) {
						$functions[] = array(
							'name' => $next_token[1],
							'line' => $next_token[2],
						);
						break;
					}
				}
			}
		}

		return $functions;
	}

	/**
	 * Check if function name follows snake_case convention
	 *
	 * @since  1.6028.1735
	 * @param  string $name Function name.
	 * @return bool True if valid snake_case.
	 */
	private static function is_valid_snake_case( string $name ): bool {
		// Valid snake_case: lowercase letters, numbers, underscores only
		// Must start with letter
		return (bool) preg_match( '/^[a-z][a-z0-9_]*$/', $name );
	}

	/**
	 * Convert function name to snake_case
	 *
	 * @since  1.6028.1735
	 * @param  string $name Function name.
	 * @return string Suggested snake_case name.
	 */
	private static function convert_to_snake_case( string $name ): string {
		// Convert camelCase to snake_case
		$name = preg_replace( '/([a-z])([A-Z])/', '$1_$2', $name );
		// Convert PascalCase to snake_case
		$name = preg_replace( '/([A-Z]+)([A-Z][a-z])/', '$1_$2', $name );
		return strtolower( $name );
	}

	/**
	 * Get violation type based on naming pattern
	 *
	 * @since  1.6028.1735
	 * @param  string $name Function name.
	 * @return string Violation type.
	 */
	private static function get_violation_type( string $name ): string {
		if ( preg_match( '/^[A-Z]/', $name ) ) {
			return 'PascalCase';
		}
		if ( preg_match( '/[A-Z]/', $name ) ) {
			return 'camelCase';
		}
		if ( preg_match( '/[^a-z0-9_]/', $name ) ) {
			return 'invalid_characters';
		}
		return 'other';
	}

	/**
	 * Build finding array
	 *
	 * @since  1.6028.1735
	 * @param  array $analysis Analysis results.
	 * @return array Finding array.
	 */
	private static function build_finding( array $analysis ): array {
		$compliance = $analysis['compliance_percentage'];
		$violations = $analysis['total_violations'];

		// Determine severity based on compliance
		if ( $compliance >= 90 ) {
			$severity = 'low';
			$threat_level = 10;
		} elseif ( $compliance >= 75 ) {
			$severity = 'medium';
			$threat_level = 15;
		} else {
			$severity = 'high';
			$threat_level = 20;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: number of violations, 2: compliance percentage */
				__( 'Found %1$d function naming violations. Naming compliance: %2$.1f%%.', 'wpshadow' ),
				$violations,
				$compliance
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false, // Requires manual refactoring
			'kb_link'     => 'https://wpshadow.com/kb/code-quality-function-naming',
			'family'      => self::$family,
			'meta'        => array(
				'total_functions'       => $analysis['total_functions'],
				'total_violations'      => $analysis['total_violations'],
				'compliance_percentage' => $compliance,
			),
			'details'     => array(
				'violations'         => $analysis['violations'],
				'violations_by_type' => $analysis['violations_by_type'],
				'recommendations'    => array(
					__( 'Use snake_case (lowercase with underscores) for all function names', 'wpshadow' ),
					__( 'Prefix functions with theme/plugin name to avoid conflicts', 'wpshadow' ),
					__( 'Refactor camelCase and PascalCase functions to snake_case', 'wpshadow' ),
					__( 'Use find-and-replace carefully to update all function calls', 'wpshadow' ),
					__( 'Consider using namespaces to avoid naming conflicts', 'wpshadow' ),
				),
			),
		);
	}
}
