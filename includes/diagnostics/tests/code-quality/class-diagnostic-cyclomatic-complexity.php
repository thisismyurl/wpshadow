<?php
/**
 * Cyclomatic Complexity Diagnostic
 *
 * Measures function complexity using McCabe score. High complexity
 * indicates refactoring opportunity and increased bug risk.
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
 * Diagnostic_Cyclomatic_Complexity Class
 *
 * Analyzes function complexity by counting decision points (if, for, while,
 * case, catch, etc.). High complexity suggests refactoring needed.
 *
 * @since 1.6028.1725
 */
class Diagnostic_Cyclomatic_Complexity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cyclomatic-complexity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cyclomatic Complexity Above 15';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures function complexity indicating refactoring opportunity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code_quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1725
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::analyze_complexity();

		if ( $analysis['high_complexity_count'] < 5 ) {
			return null; // Acceptable complexity levels.
		}

		// Determine severity based on count and average.
		if ( $analysis['high_complexity_count'] > 10 || $analysis['average_complexity'] > 15 ) {
			$severity     = 'low';
			$threat_level = 40;
		} elseif ( $analysis['high_complexity_count'] > 5 ) {
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
				/* translators: 1: number of high-complexity functions, 2: average complexity */
				__( 'Found %1$d functions with complexity >15 (avg: %2$d)', 'wpshadow' ),
				$analysis['high_complexity_count'],
				$analysis['average_complexity']
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cyclomatic-complexity',
			'family'      => self::$family,
			'meta'        => array(
				'affected_count'       => $analysis['high_complexity_count'],
				'average_complexity'   => $analysis['average_complexity'],
				'highest_complexity'   => $analysis['highest_complexity'],
				'recommended'          => __( 'Average complexity <10, <5 functions >15', 'wpshadow' ),
				'impact_level'         => 'low',
				'immediate_actions'    => array(
					__( 'Review highest complexity functions', 'wpshadow' ),
					__( 'Reduce conditional nesting', 'wpshadow' ),
					__( 'Extract complex logic to methods', 'wpshadow' ),
					__( 'Simplify boolean expressions', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Cyclomatic complexity measures the number of independent paths through code. High complexity (>15) indicates too many decision points, making code hard to test, debug, and maintain. Each decision point doubles the number of test cases needed for full coverage. Complexity >15 is considered high risk for bugs.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Bug Risk: More complexity = exponentially more bugs', 'wpshadow' ),
					__( 'Test Burden: High complexity requires many test cases', 'wpshadow' ),
					__( 'Hard to Understand: Developers struggle with logic flow', 'wpshadow' ),
					__( 'Maintenance Cost: Changes risky without full test coverage', 'wpshadow' ),
				),
				'complexity_analysis' => array(
					'high_complexity_count' => $analysis['high_complexity_count'],
					'average_complexity'    => $analysis['average_complexity'],
					'highest_complexity'    => $analysis['highest_complexity'],
					'examples'              => $analysis['examples'],
				),
				'complexity_scale' => array(
					'1-10'  => __( 'Simple function, low risk', 'wpshadow' ),
					'11-20' => __( 'Moderate complexity, consider refactoring', 'wpshadow' ),
					'21-50' => __( 'High complexity, refactoring recommended', 'wpshadow' ),
					'>50'   => __( 'Very high complexity, critical refactoring needed', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Reduce Nesting Depth', 'wpshadow' ),
						'description' => __( 'Flatten nested conditionals using guard clauses', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify deeply nested if/else blocks', 'wpshadow' ),
							__( 'Use early returns for error conditions', 'wpshadow' ),
							__( 'Invert conditions to reduce nesting', 'wpshadow' ),
							__( 'Extract nested logic to helper methods', 'wpshadow' ),
							__( 'Test function still works correctly', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Extract Complex Logic', 'wpshadow' ),
						'description' => __( 'Move complex conditionals to dedicated methods', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify complex boolean expressions', 'wpshadow' ),
							__( 'Extract to method with descriptive name', 'wpshadow' ),
							__( 'Example: is_valid_user() vs if($user && $user->active)', 'wpshadow' ),
							__( 'Simplify main function logic', 'wpshadow' ),
							__( 'Test each extracted method', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Replace Conditionals with Polymorphism', 'wpshadow' ),
						'description' => __( 'Use strategy pattern or polymorphism instead of switch', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify large switch/if-else chains', 'wpshadow' ),
							__( 'Create interface for behavior', 'wpshadow' ),
							__( 'Implement class for each condition', 'wpshadow' ),
							__( 'Replace conditionals with polymorphic calls', 'wpshadow' ),
							__( 'Complexity drops to O(1) from O(n)', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Target complexity <10 for most functions', 'wpshadow' ),
					__( 'Use early returns to reduce nesting', 'wpshadow' ),
					__( 'Extract complex boolean logic to named methods', 'wpshadow' ),
					__( 'Avoid switch statements with >5 cases', 'wpshadow' ),
					__( 'Measure complexity with PHP_CodeSniffer', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Run this diagnostic after refactoring', 'wpshadow' ),
						__( 'Use phpmetrics or phploc for detailed analysis', 'wpshadow' ),
						__( 'Add PHPCS rule: Generic.Metrics.CyclomaticComplexity', 'wpshadow' ),
						__( 'Monitor code coverage improvements', 'wpshadow' ),
					),
					'expected_result' => __( 'Average complexity <10, <5 functions >15', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze cyclomatic complexity in theme and plugin files.
	 *
	 * @since  1.6028.1725
	 * @return array Analysis results with counts and examples.
	 */
	private static function analyze_complexity() {
		$result = array(
			'high_complexity_count' => 0,
			'average_complexity'    => 0,
			'highest_complexity'    => array(
				'name'       => '',
				'complexity' => 0,
				'file'       => '',
			),
			'examples'              => array(),
		);

		// Get active theme directory.
		$theme_dir = get_stylesheet_directory();
		$files     = self::get_php_files( $theme_dir );

		// Add active plugins (first 5 for performance).
		$active_plugins = array_slice( get_option( 'active_plugins', array() ), 0, 5 );
		foreach ( $active_plugins as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			$plugin_dir  = dirname( $plugin_file );
			$files       = array_merge( $files, self::get_php_files( $plugin_dir ) );
		}

		$total_complexity = 0;
		$function_count   = 0;
		$example_limit    = 10;

		foreach ( $files as $file ) {
			$content = @file_get_contents( $file );
			if ( $content === false ) {
				continue;
			}

			$functions = self::calculate_complexity( $content );

			foreach ( $functions as $func ) {
				$function_count++;
				$total_complexity += $func['complexity'];

				if ( $func['complexity'] > 15 ) {
					$result['high_complexity_count']++;

					if ( $func['complexity'] > $result['highest_complexity']['complexity'] ) {
						$result['highest_complexity'] = array(
							'name'       => $func['name'],
							'complexity' => $func['complexity'],
							'file'       => str_replace( ABSPATH, '', $file ),
						);
					}

					if ( count( $result['examples'] ) < $example_limit ) {
						$result['examples'][] = array(
							'name'       => $func['name'],
							'complexity' => $func['complexity'],
							'file'       => str_replace( ABSPATH, '', $file ),
						);
					}
				}
			}
		}

		if ( $function_count > 0 ) {
			$result['average_complexity'] = (int) ( $total_complexity / $function_count );
		}

		return $result;
	}

	/**
	 * Calculate cyclomatic complexity for functions in content.
	 *
	 * Complexity = 1 + number of decision points (if, for, while, case, catch, &&, ||).
	 *
	 * @since  1.6028.1725
	 * @param  string $content PHP file content.
	 * @return array Array of functions with complexity scores.
	 */
	private static function calculate_complexity( $content ) {
		$functions = array();
		$tokens    = @token_get_all( $content );
		if ( empty( $tokens ) ) {
			return $functions;
		}

		$in_function = false;
		$func_name   = '';
		$complexity  = 1; // Base complexity is 1.
		$brace_depth = 0;

		foreach ( $tokens as $index => $token ) {
			if ( ! is_array( $token ) ) {
				// Count braces to detect function end.
				if ( $token === '{' && $in_function ) {
					$brace_depth++;
				} elseif ( $token === '}' && $in_function ) {
					$brace_depth--;
					if ( $brace_depth === 0 ) {
						$functions[] = array(
							'name'       => $func_name,
							'complexity' => $complexity,
						);
						$in_function = false;
						$complexity  = 1;
					}
				}
				continue;
			}

			// Detect function start.
			if ( $token[0] === T_FUNCTION ) {
				// Get function name.
				$next_token = $tokens[ $index + 2 ] ?? null;
				if ( $next_token && is_array( $next_token ) && $next_token[0] === T_STRING ) {
					$func_name   = $next_token[1];
					$in_function = true;
					$complexity  = 1;
					$brace_depth = 0;
				}
			}

			if ( $in_function ) {
				// Count decision points.
				$decision_tokens = array(
					T_IF,
					T_ELSEIF,
					T_FOR,
					T_FOREACH,
					T_WHILE,
					T_DO,
					T_CASE,
					T_CATCH,
					T_BOOLEAN_AND,
					T_BOOLEAN_OR,
					T_LOGICAL_AND,
					T_LOGICAL_OR,
				);

				if ( in_array( $token[0], $decision_tokens, true ) ) {
					$complexity++;
				}
			}
		}

		return $functions;
	}

	/**
	 * Get all PHP files in a directory recursively.
	 *
	 * @since  1.6028.1725
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

		return array_slice( $files, 0, 50 ); // Limit for performance.
	}
}
