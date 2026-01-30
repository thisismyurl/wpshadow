<?php
/**
 * Code Comments Coverage Diagnostic
 *
 * Measures code documentation level. Low comment ratio indicates poor
 * maintainability and difficulty with knowledge transfer.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1730
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Code_Comments_Coverage Class
 *
 * Analyzes comment density across theme and plugin code to measure
 * documentation quality and maintainability.
 *
 * @since 1.6028.1730
 */
class Diagnostic_Code_Comments_Coverage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'code-comments-coverage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Code Comments Below 10% of Codebase';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures code documentation level indicating maintainability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code_quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1730
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::analyze_comments();

		if ( $analysis['comment_percentage'] >= 10 ) {
			return null; // Acceptable comment coverage.
		}

		// Determine severity based on percentage.
		if ( $analysis['comment_percentage'] < 5 ) {
			$severity     = 'low';
			$threat_level = 35;
		} else {
			$severity     = 'info';
			$threat_level = 25;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: comment percentage */
				__( 'Code has only %s%% comments, indicating poor documentation', 'wpshadow' ),
				number_format( $analysis['comment_percentage'], 1 )
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/code-comments',
			'family'      => self::$family,
			'meta'        => array(
				'comment_percentage'    => round( $analysis['comment_percentage'], 1 ),
				'comment_lines'         => $analysis['comment_lines'],
				'code_lines'            => $analysis['code_lines'],
				'undocumented_functions' => $analysis['undocumented_functions'],
				'recommended'           => __( '>15% comments for maintainability', 'wpshadow' ),
				'impact_level'          => 'low',
				'immediate_actions'     => array(
					__( 'Document public functions/methods', 'wpshadow' ),
					__( 'Add docblocks with @param/@return', 'wpshadow' ),
					__( 'Comment complex logic sections', 'wpshadow' ),
					__( 'Use PHPDoc standards', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Code comments preserve knowledge, explain complex logic, and improve maintainability. Low comment coverage makes onboarding slow, increases bug risk from misunderstanding, and reduces team velocity. Industry standard is 15-20% comment density for professional codebases.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Slow Onboarding: New developers struggle to understand code', 'wpshadow' ),
					__( 'Bug Risk: Misunderstood logic leads to incorrect changes', 'wpshadow' ),
					__( 'Knowledge Loss: Key decisions not documented', 'wpshadow' ),
					__( 'Maintenance Cost: More time spent understanding vs fixing', 'wpshadow' ),
				),
				'comment_analysis' => array(
					'comment_percentage'      => round( $analysis['comment_percentage'], 1 ),
					'comment_lines'           => $analysis['comment_lines'],
					'code_lines'              => $analysis['code_lines'],
					'undocumented_functions'  => $analysis['undocumented_functions'],
					'docblock_coverage'       => round( $analysis['docblock_coverage'], 1 ),
				),
				'comment_scale' => array(
					'>20%'  => __( 'Excellent: Professional documentation level', 'wpshadow' ),
					'15-20%' => __( 'Good: Adequate maintainability', 'wpshadow' ),
					'10-15%' => __( 'Acceptable: Minimal documentation', 'wpshadow' ),
					'<10%'  => __( 'Poor: Insufficient documentation', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Add PHPDoc Blocks', 'wpshadow' ),
						'description' => __( 'Document functions with standard PHPDoc format', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify undocumented public functions', 'wpshadow' ),
							__( 'Add docblock above each function', 'wpshadow' ),
							__( 'Include description, @param, @return, @since', 'wpshadow' ),
							__( 'Follow WordPress documentation standards', 'wpshadow' ),
							__( 'Run this diagnostic to track progress', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Inline Logic Comments', 'wpshadow' ),
						'description' => __( 'Explain complex logic with inline comments', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify complex conditional logic', 'wpshadow' ),
							__( 'Add "why" comments before sections', 'wpshadow' ),
							__( 'Explain non-obvious behavior', 'wpshadow' ),
							__( 'Document edge cases and workarounds', 'wpshadow' ),
							__( 'Avoid obvious comments like "increment i"', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Auto-Generate with AI/Tools', 'wpshadow' ),
						'description' => __( 'Use phpDocumentor or AI to generate docs', 'wpshadow' ),
						'steps'       => array(
							__( 'Run phpDocumentor to generate skeleton docs', 'wpshadow' ),
							__( 'Or use AI: "Generate PHPDoc for this function"', 'wpshadow' ),
							__( 'Review generated comments for accuracy', 'wpshadow' ),
							__( 'Add business context AI can\'t infer', 'wpshadow' ),
							__( 'Commit updated documentation', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Every public function needs docblock', 'wpshadow' ),
					__( 'Comment "why" not "what" (code shows what)', 'wpshadow' ),
					__( 'Document edge cases and assumptions', 'wpshadow' ),
					__( 'Use @param, @return, @throws, @since tags', 'wpshadow' ),
					__( 'Keep comments up-to-date with code changes', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Run this diagnostic after adding comments', 'wpshadow' ),
						__( 'Use phpDocumentor to validate PHPDoc syntax', 'wpshadow' ),
						__( 'Check WordPress Coding Standards compliance', 'wpshadow' ),
						__( 'Review documentation completeness manually', 'wpshadow' ),
					),
					'expected_result' => __( '>15% comment coverage, all public functions documented', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze comment coverage in theme and plugin files.
	 *
	 * @since  1.6028.1730
	 * @return array Analysis results with counts and percentages.
	 */
	private static function analyze_comments() {
		$result = array(
			'comment_lines'           => 0,
			'code_lines'              => 0,
			'comment_percentage'      => 0,
			'undocumented_functions'  => 0,
			'total_functions'         => 0,
			'docblock_coverage'       => 0,
		);

		// Get active theme directory.
		$theme_dir = get_stylesheet_directory();
		$files     = self::get_php_files( $theme_dir );

		// Add active plugins.
		$active_plugins = array_slice( get_option( 'active_plugins', array() ), 0, 5 );
		foreach ( $active_plugins as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			$plugin_dir  = dirname( $plugin_file );
			$files       = array_merge( $files, self::get_php_files( $plugin_dir ) );
		}

		foreach ( $files as $file ) {
			$content = @file_get_contents( $file );
			if ( $content === false ) {
				continue;
			}

			$file_stats = self::count_lines( $content );
			$result['comment_lines'] += $file_stats['comment_lines'];
			$result['code_lines']    += $file_stats['code_lines'];

			$function_stats = self::analyze_function_docs( $content );
			$result['undocumented_functions'] += $function_stats['undocumented'];
			$result['total_functions']        += $function_stats['total'];
		}

		if ( $result['code_lines'] > 0 ) {
			$result['comment_percentage'] = ( $result['comment_lines'] / $result['code_lines'] ) * 100;
		}

		if ( $result['total_functions'] > 0 ) {
			$documented = $result['total_functions'] - $result['undocumented_functions'];
			$result['docblock_coverage'] = ( $documented / $result['total_functions'] ) * 100;
		}

		return $result;
	}

	/**
	 * Count comment lines and code lines in file.
	 *
	 * @since  1.6028.1730
	 * @param  string $content File content.
	 * @return array Line counts.
	 */
	private static function count_lines( $content ) {
		$lines = explode( "\n", $content );
		
		$comment_lines = 0;
		$code_lines    = 0;
		$in_comment    = false;

		foreach ( $lines as $line ) {
			$trimmed = trim( $line );

			// Skip empty lines.
			if ( empty( $trimmed ) ) {
				continue;
			}

			// Check for comment blocks.
			if ( strpos( $trimmed, '/*' ) === 0 ) {
				$in_comment = true;
			}

			if ( $in_comment ) {
				$comment_lines++;
				if ( strpos( $trimmed, '*/' ) !== false ) {
					$in_comment = false;
				}
				continue;
			}

			// Check for single-line comments.
			if ( strpos( $trimmed, '//' ) === 0 || strpos( $trimmed, '#' ) === 0 ) {
				$comment_lines++;
				continue;
			}

			// Otherwise count as code.
			$code_lines++;
		}

		return array(
			'comment_lines' => $comment_lines,
			'code_lines'    => $code_lines,
		);
	}

	/**
	 * Analyze function documentation coverage.
	 *
	 * @since  1.6028.1730
	 * @param  string $content File content.
	 * @return array Function documentation stats.
	 */
	private static function analyze_function_docs( $content ) {
		$lines = explode( "\n", $content );
		
		$total         = 0;
		$undocumented  = 0;

		foreach ( $lines as $index => $line ) {
			// Detect function declaration.
			if ( preg_match( '/\bfunction\s+[a-zA-Z_\x7f-\xff]/', $line ) ) {
				$total++;

				// Check if previous lines have docblock.
				$has_docblock = false;
				for ( $i = $index - 1; $i >= max( 0, $index - 5 ); $i-- ) {
					$prev = trim( $lines[ $i ] );
					if ( strpos( $prev, '/**' ) !== false ) {
						$has_docblock = true;
						break;
					}
					if ( ! empty( $prev ) && strpos( $prev, '*' ) !== 0 && strpos( $prev, '//' ) !== 0 ) {
						break; // Found non-comment code, no docblock.
					}
				}

				if ( ! $has_docblock ) {
					$undocumented++;
				}
			}
		}

		return array(
			'total'         => $total,
			'undocumented'  => $undocumented,
		);
	}

	/**
	 * Get all PHP files in a directory recursively.
	 *
	 * @since  1.6028.1730
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
