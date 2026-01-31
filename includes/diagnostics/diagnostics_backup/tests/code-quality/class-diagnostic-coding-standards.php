<?php
/**
 * WordPress Coding Standards Compliance Diagnostic
 *
 * Analyzes custom theme and plugin code for WordPress coding standards compliance.
 * Detects violations in spacing, naming, indentation, and documentation.
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
 * Coding Standards Compliance Diagnostic Class
 *
 * Scans custom theme and plugin code for WordPress coding standards violations.
 * Reports compliance percentage and lists top violations.
 *
 * @since 1.6028.1730
 */
class Diagnostic_Coding_Standards extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1730
	 * @var   string
	 */
	protected static $slug = 'coding-standards-compliance';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1730
	 * @var   string
	 */
	protected static $title = 'WordPress Coding Standards Compliance';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1730
	 * @var   string
	 */
	protected static $description = 'Checks if custom theme and plugin code follows WordPress coding standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1730
	 * @var   string
	 */
	protected static $family = 'code-quality';

	/**
	 * Cache duration in seconds (6 hours)
	 *
	 * @since 1.6028.1730
	 */
	private const CACHE_DURATION = 21600;

	/**
	 * Common coding standard violations to check
	 *
	 * @since 1.6028.1730
	 */
	private const VIOLATION_PATTERNS = array(
		'spacing_operators'   => array(
			'pattern'     => '/([^\s])([+\-*\/%=<>!&|^]+)([^\s])/',
			'description' => 'Operators should have spaces around them',
			'severity'    => 'minor',
		),
		'spacing_keywords'    => array(
			'pattern'     => '/(if|for|foreach|while|switch)\(/',
			'description' => 'Control structures should have space before parenthesis',
			'severity'    => 'minor',
		),
		'yoda_condition'      => array(
			'pattern'     => '/\$[a-zA-Z_][a-zA-Z0-9_]*\s*[!=]=\s*[\'"]/',
			'description' => 'Use Yoda conditions (constant first)',
			'severity'    => 'minor',
		),
		'lowercase_keywords'  => array(
			'pattern'     => '/\b(TRUE|FALSE|NULL)\b/',
			'description' => 'Use lowercase for true, false, null',
			'severity'    => 'minor',
		),
		'function_spacing'    => array(
			'pattern'     => '/function\s+[a-zA-Z_][a-zA-Z0-9_]*\s*\(/',
			'description' => 'No space between function name and parenthesis',
			'severity'    => 'minor',
			'invert'      => true, // Should NOT have space
		),
		'brace_placement'     => array(
			'pattern'     => '/\)\s*\n\s*{/',
			'description' => 'Opening brace should be on same line',
			'severity'    => 'minor',
		),
		'array_short_syntax'  => array(
			'pattern'     => '/\barray\s*\(/',
			'description' => 'Use short array syntax []',
			'severity'    => 'minor',
		),
		'single_quotes'       => array(
			'pattern'     => '/"[^"$\\\\{]*"/',
			'description' => 'Use single quotes unless interpolation needed',
			'severity'    => 'minor',
		),
		'indentation_tabs'    => array(
			'pattern'     => '/^[ ]{4,}[^\s]/',
			'description' => 'Use tabs for indentation, not spaces',
			'severity'    => 'minor',
		),
		'missing_docblock'    => array(
			'pattern'     => '/^\s*(public|protected|private)\s+(static\s+)?function/',
			'description' => 'Methods should have docblock',
			'severity'    => 'minor',
		),
	);

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6028.1730
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Check cache first
		$cache_key = 'wpshadow_diagnostic_coding_standards';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			if ( null === $cached ) {
				return null;
			}
			return self::build_finding( $cached );
		}

		// Analyze coding standards
		$analysis = self::analyze_coding_standards();

		// Cache result
		set_transient( $cache_key, $analysis, self::CACHE_DURATION );

		if ( null === $analysis ) {
			return null;
		}

		return self::build_finding( $analysis );
	}

	/**
	 * Analyze coding standards compliance
	 *
	 * @since  1.6028.1730
	 * @return array|null Analysis results or null if compliant.
	 */
	private static function analyze_coding_standards() {
		// Get custom theme and plugin files
		$files_to_scan = self::get_custom_files();

		if ( empty( $files_to_scan ) ) {
			return null;
		}

		$total_violations = 0;
		$total_lines      = 0;
		$violations_by_type = array();
		$files_with_violations = array();

		foreach ( $files_to_scan as $file_path ) {
			$violations = self::scan_file_for_violations( $file_path );

			if ( ! empty( $violations['violations'] ) ) {
				$total_violations += count( $violations['violations'] );
				$files_with_violations[] = array(
					'file'       => str_replace( ABSPATH, '', $file_path ),
					'violations' => count( $violations['violations'] ),
					'details'    => array_slice( $violations['violations'], 0, 5 ), // Top 5
				);

				// Group by type
				foreach ( $violations['violations'] as $violation ) {
					$type = $violation['type'];
					if ( ! isset( $violations_by_type[ $type ] ) ) {
						$violations_by_type[ $type ] = 0;
					}
					++$violations_by_type[ $type ];
				}
			}

			$total_lines += $violations['total_lines'];
		}

		// No violations found
		if ( 0 === $total_violations ) {
			return null;
		}

		// Calculate compliance percentage
		$violations_per_100_lines = ( $total_lines > 0 ) ? ( $total_violations / $total_lines ) * 100 : 0;
		$compliance_percentage    = max( 0, 100 - $violations_per_100_lines );

		// Sort violations by frequency
		arsort( $violations_by_type );

		// Sort files by violation count
		usort( $files_with_violations, function( $a, $b ) {
			return $b['violations'] <=> $a['violations'];
		});

		return array(
			'total_violations'         => $total_violations,
			'total_lines'              => $total_lines,
			'compliance_percentage'    => round( $compliance_percentage, 1 ),
			'violations_by_type'       => $violations_by_type,
			'files_with_violations'    => array_slice( $files_with_violations, 0, 10 ), // Top 10
			'total_files_scanned'      => count( $files_to_scan ),
			'total_files_with_issues'  => count( $files_with_violations ),
		);
	}

	/**
	 * Get custom theme and plugin PHP files
	 *
	 * @since  1.6028.1730
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

		// Get custom plugin files (exclude vendor/node_modules)
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
	 * @since  1.6028.1730
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

				// Limit to first 50 files per directory to avoid timeout
				if ( count( $files ) >= 50 ) {
					break;
				}

				$files[] = $file_path;
			}
		} catch ( \Exception $e ) {
			// Directory access error
			return array();
		}

		return $files;
	}

	/**
	 * Scan file for coding standards violations
	 *
	 * @since  1.6028.1730
	 * @param  string $file_path File path.
	 * @return array Violations and line count.
	 */
	private static function scan_file_for_violations( string $file_path ): array {
		$content = @file_get_contents( $file_path );
		if ( false === $content ) {
			return array(
				'violations'  => array(),
				'total_lines' => 0,
			);
		}

		$lines = explode( "\n", $content );
		$total_lines = count( $lines );
		$violations = array();

		foreach ( self::VIOLATION_PATTERNS as $type => $pattern_data ) {
			$pattern = $pattern_data['pattern'];
			$invert = isset( $pattern_data['invert'] ) && $pattern_data['invert'];

			foreach ( $lines as $line_num => $line ) {
				$matches = preg_match( $pattern, $line );

				if ( false === $matches ) {
					continue;
				}

				// For inverted patterns, match means violation
				// For normal patterns, match means violation
				if ( ( $invert && $matches > 0 ) || ( ! $invert && $matches > 0 ) ) {
					$violations[] = array(
						'type'        => $type,
						'line'        => $line_num + 1,
						'description' => $pattern_data['description'],
						'severity'    => $pattern_data['severity'],
						'preview'     => trim( $line ),
					);
				}
			}
		}

		return array(
			'violations'  => $violations,
			'total_lines' => $total_lines,
		);
	}

	/**
	 * Build finding array
	 *
	 * @since  1.6028.1730
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
			$threat_level = 25;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: number of violations, 2: compliance percentage */
				__( 'Found %1$d coding standards violations. Code compliance: %2$.1f%%.', 'wpshadow' ),
				$violations,
				$compliance
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => true, // PHPCS can auto-fix some issues
			'kb_link'     => 'https://wpshadow.com/kb/code-quality-coding-standards',
			'family'      => self::$family,
			'meta'        => array(
				'total_violations'        => $analysis['total_violations'],
				'total_lines'             => $analysis['total_lines'],
				'compliance_percentage'   => $compliance,
				'total_files_scanned'     => $analysis['total_files_scanned'],
				'total_files_with_issues' => $analysis['total_files_with_issues'],
			),
			'details'     => array(
				'violations_by_type'    => $analysis['violations_by_type'],
				'files_with_violations' => $analysis['files_with_violations'],
				'recommendations'       => array(
					__( 'Install and configure PHP_CodeSniffer (PHPCS) with WordPress Coding Standards', 'wpshadow' ),
					__( 'Run automatic code fixes using PHPCBF (PHP Code Beautifier)', 'wpshadow' ),
					__( 'Configure your IDE to show coding standard warnings in real-time', 'wpshadow' ),
					__( 'Focus on fixing high-frequency violations first for maximum impact', 'wpshadow' ),
					__( 'Add pre-commit hooks to prevent new violations from being committed', 'wpshadow' ),
				),
			),
		);
	}
}
