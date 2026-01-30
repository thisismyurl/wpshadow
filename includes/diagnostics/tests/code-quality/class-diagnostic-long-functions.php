<?php
/**
 * Long Functions Diagnostic
 *
 * Identifies functions exceeding 100 lines, indicating poor separation
 * of concerns and refactoring opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1720
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Long_Functions Class
 *
 * Scans theme and plugin code for functions/methods over 100 lines.
 * Identifies code that violates single responsibility principle.
 *
 * @since 1.6028.1720
 */
class Diagnostic_Long_Functions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'long-functions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Long Functions Over 100 Lines';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies functions exceeding 100 lines indicating poor separation of concerns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code_quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1720
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::analyze_function_length();

		if ( $analysis['long_functions'] < 5 ) {
			return null; // Acceptable number of long functions.
		}

		// Determine severity based on count.
		if ( $analysis['long_functions'] > 20 ) {
			$severity     = 'low';
			$threat_level = 40;
		} elseif ( $analysis['long_functions'] > 10 ) {
			$severity     = 'info';
			$threat_level = 30;
		} else {
			$severity     = 'info';
			$threat_level = 20;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: number of long functions, 2: average lines */
				__( 'Found %1$d functions over 100 lines (avg: %2$d lines)', 'wpshadow' ),
				$analysis['long_functions'],
				$analysis['average_length']
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/function-length',
			'family'      => self::$family,
			'meta'        => array(
				'affected_count'    => $analysis['long_functions'],
				'average_length'    => $analysis['average_length'],
				'longest_function'  => $analysis['longest_function'],
				'recommended'       => __( '<5 functions over 100 lines', 'wpshadow' ),
				'impact_level'      => 'low',
				'immediate_actions' => array(
					__( 'Review longest functions', 'wpshadow' ),
					__( 'Extract helper methods', 'wpshadow' ),
					__( 'Apply single responsibility principle', 'wpshadow' ),
					__( 'Add unit tests before refactoring', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Long functions violate the Single Responsibility Principle and are harder to understand, test, and maintain. Functions should do one thing well. When a function exceeds 100 lines, it\'s usually doing multiple things and should be split into smaller, focused functions.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Hard to Test: Large functions make unit testing difficult', 'wpshadow' ),
					__( 'Hard to Debug: More lines = more potential bugs', 'wpshadow' ),
					__( 'Poor Reusability: Specific logic can\'t be reused elsewhere', 'wpshadow' ),
					__( 'Cognitive Load: Developers must understand more context', 'wpshadow' ),
				),
				'function_analysis' => array(
					'long_functions'   => $analysis['long_functions'],
					'average_length'   => $analysis['average_length'],
					'longest_function' => $analysis['longest_function'],
					'examples'         => $analysis['examples'],
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Extract Method Refactoring', 'wpshadow' ),
						'description' => __( 'Break large functions into smaller helper methods', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify logical sections within long function', 'wpshadow' ),
							__( 'Extract section to private helper method', 'wpshadow' ),
							__( 'Give helper descriptive name (what it does)', 'wpshadow' ),
							__( 'Replace section with helper call', 'wpshadow' ),
							__( 'Test function still works correctly', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Single Responsibility Refactoring', 'wpshadow' ),
						'description' => __( 'Split function into multiple focused functions', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify distinct responsibilities in function', 'wpshadow' ),
							__( 'Create new function for each responsibility', 'wpshadow' ),
							__( 'Move related code to appropriate function', 'wpshadow' ),
							__( 'Original function orchestrates calls', 'wpshadow' ),
							__( 'Test each new function independently', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Strategy Pattern Implementation', 'wpshadow' ),
						'description' => __( 'Replace conditional logic with strategy classes', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify conditional branches (if/switch)', 'wpshadow' ),
							__( 'Create strategy interface', 'wpshadow' ),
							__( 'Implement strategy for each branch', 'wpshadow' ),
							__( 'Replace conditionals with strategy selection', 'wpshadow' ),
							__( 'Test all strategies independently', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Target 20-40 lines per function (ideal)', 'wpshadow' ),
					__( 'Functions should do one thing and do it well', 'wpshadow' ),
					__( 'Extract helper methods for logical sections', 'wpshadow' ),
					__( 'Use descriptive function names', 'wpshadow' ),
					__( 'Write tests before refactoring for safety', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Run this diagnostic after refactoring', 'wpshadow' ),
						__( 'Write unit tests for extracted methods', 'wpshadow' ),
						__( 'Use PHP_CodeSniffer for complexity checks', 'wpshadow' ),
						__( 'Monitor code coverage improvements', 'wpshadow' ),
					),
					'expected_result' => __( '<5 functions over 100 lines', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze function length in theme and plugin files.
	 *
	 * @since  1.6028.1720
	 * @return array Analysis results with counts and examples.
	 */
	private static function analyze_function_length() {
		$result = array(
			'long_functions'   => 0,
			'average_length'   => 0,
			'longest_function' => array(
				'name'  => '',
				'lines' => 0,
				'file'  => '',
			),
			'examples'         => array(),
		);

		// Get active theme directory.
		$theme_dir = get_stylesheet_directory();
		$files     = self::get_php_files( $theme_dir );

		// Add active plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $active_plugins as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			$plugin_dir  = dirname( $plugin_file );
			$files       = array_merge( $files, self::get_php_files( $plugin_dir ) );
		}

		$total_length  = 0;
		$example_limit = 10;

		foreach ( $files as $file ) {
			$content = @file_get_contents( $file );
			if ( $content === false ) {
				continue;
			}

			$functions = self::extract_functions( $content );

			foreach ( $functions as $func ) {
				if ( $func['lines'] > 100 ) {
					$result['long_functions']++;
					$total_length += $func['lines'];

					if ( $func['lines'] > $result['longest_function']['lines'] ) {
						$result['longest_function'] = array(
							'name'  => $func['name'],
							'lines' => $func['lines'],
							'file'  => str_replace( ABSPATH, '', $file ),
						);
					}

					if ( count( $result['examples'] ) < $example_limit ) {
						$result['examples'][] = array(
							'name'  => $func['name'],
							'lines' => $func['lines'],
							'file'  => str_replace( ABSPATH, '', $file ),
						);
					}
				}
			}
		}

		if ( $result['long_functions'] > 0 ) {
			$result['average_length'] = (int) ( $total_length / $result['long_functions'] );
		}

		return $result;
	}

	/**
	 * Extract functions from PHP content.
	 *
	 * @since  1.6028.1720
	 * @param  string $content PHP file content.
	 * @return array Array of functions with names and line counts.
	 */
	private static function extract_functions( $content ) {
		$functions = array();
		$lines     = explode( "\n", $content );
		$in_function = false;
		$func_name   = '';
		$func_start  = 0;
		$brace_count = 0;

		foreach ( $lines as $line_num => $line ) {
			// Detect function start.
			if ( preg_match( '/function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/', $line, $matches ) ) {
				$in_function = true;
				$func_name   = $matches[1];
				$func_start  = $line_num + 1;
				$brace_count = 0;
			}

			if ( $in_function ) {
				// Count braces to find function end.
				$brace_count += substr_count( $line, '{' );
				$brace_count -= substr_count( $line, '}' );

				if ( $brace_count === 0 && strpos( $line, '}' ) !== false ) {
					$func_end    = $line_num + 1;
					$func_length = $func_end - $func_start;

					$functions[] = array(
						'name'  => $func_name,
						'lines' => $func_length,
					);

					$in_function = false;
				}
			}
		}

		return $functions;
	}

	/**
	 * Get all PHP files in a directory recursively.
	 *
	 * @since  1.6028.1720
	 * @param  string $dir Directory path.
	 * @return array Array of file paths.
	 */
	private static function get_php_files( $dir ) {
		$files = array();

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				// Skip vendor directories.
				if ( strpos( $file->getPathname(), '/vendor/' ) !== false ) {
					continue;
				}
				$files[] = $file->getPathname();
			}
		}

		return array_slice( $files, 0, 100 ); // Limit for performance.
	}
}
