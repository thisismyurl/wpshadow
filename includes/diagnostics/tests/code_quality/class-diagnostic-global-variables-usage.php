<?php
/**
 * Global Variables Anti-pattern Diagnostic
 *
 * Detects excessive global variable usage indicating namespace pollution
 * and architectural issues. Recommends class-based approach.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1715
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Global_Variables_Usage Class
 *
 * Scans theme and plugin files for global variable declarations.
 * Identifies namespace pollution and refactoring opportunities.
 *
 * @since 1.6028.1715
 */
class Diagnostic_Global_Variables_Usage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'global-variables-usage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Global Variables Anti-pattern Usage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects excessive global variable usage indicating architectural issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code_quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1715
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::analyze_global_variables();

		if ( $analysis['custom_globals'] < 3 ) {
			return null; // Acceptable global variable usage.
		}

		// Determine severity based on count.
		if ( $analysis['custom_globals'] > 10 ) {
			$severity     = 'low';
			$threat_level = 40;
		} elseif ( $analysis['custom_globals'] > 5 ) {
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
				/* translators: %d: number of custom global variables */
				__( 'Found %d custom global variables, indicating namespace pollution', 'wpshadow' ),
				$analysis['custom_globals']
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/global-variables',
			'family'      => self::$family,
			'meta'        => array(
				'affected_count'    => $analysis['custom_globals'],
				'total_declarations' => $analysis['total_declarations'],
				'files_affected'    => $analysis['files_affected'],
				'recommended'       => __( '<3 custom globals, use classes/namespaces', 'wpshadow' ),
				'impact_level'      => 'low',
				'immediate_actions' => array(
					__( 'Review custom global variables', 'wpshadow' ),
					__( 'Refactor to class-based approach', 'wpshadow' ),
					__( 'Use namespaces for organization', 'wpshadow' ),
					__( 'Follow WordPress coding standards', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Global variables pollute the global namespace, create naming conflicts, make code harder to test, and indicate poor architectural design. WordPress uses some globals ($wpdb, $post) which is acceptable, but custom code should use classes, namespaces, and dependency injection instead.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Naming Conflicts: Variables clash with plugins/themes', 'wpshadow' ),
					__( 'Hard to Test: Global state makes unit testing difficult', 'wpshadow' ),
					__( 'Poor Maintainability: Unclear data flow and dependencies', 'wpshadow' ),
					__( 'Security Risk: Global variables accessible everywhere', 'wpshadow' ),
				),
				'global_analysis' => array(
					'custom_globals'      => $analysis['custom_globals'],
					'total_declarations'  => $analysis['total_declarations'],
					'files_affected'      => $analysis['files_affected'],
					'examples'            => $analysis['examples'],
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Refactor to Classes', 'wpshadow' ),
						'description' => __( 'Convert global variables to class properties', 'wpshadow' ),
						'steps'       => array(
							__( 'Create class to encapsulate related data', 'wpshadow' ),
							__( 'Convert global variables to class properties', 'wpshadow' ),
							__( 'Use static properties for singleton pattern', 'wpshadow' ),
							__( 'Replace global declarations with class access', 'wpshadow' ),
							__( 'Test for regressions', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Use Dependency Injection', 'wpshadow' ),
						'description' => __( 'Pass dependencies explicitly instead of globals', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify dependencies (database, services, etc)', 'wpshadow' ),
							__( 'Pass dependencies via constructor', 'wpshadow' ),
							__( 'Use dependency injection container (PHP-DI, Pimple)', 'wpshadow' ),
							__( 'Remove global declarations', 'wpshadow' ),
							__( 'Update function signatures to accept dependencies', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Service Locator Pattern', 'wpshadow' ),
						'description' => __( 'Centralize service access through registry', 'wpshadow' ),
						'steps'       => array(
							__( 'Create ServiceLocator or Registry class', 'wpshadow' ),
							__( 'Register services: ServiceLocator::set(\'db\', $wpdb)', 'wpshadow' ),
							__( 'Access via: ServiceLocator::get(\'db\')', 'wpshadow' ),
							__( 'Replace global declarations with service calls', 'wpshadow' ),
							__( 'Test service resolution', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Use classes and namespaces for code organization', 'wpshadow' ),
					__( 'Pass dependencies explicitly via constructors', 'wpshadow' ),
					__( 'Use static class properties for shared state', 'wpshadow' ),
					__( 'Acceptable WordPress globals: $wpdb, $post, $wp_query', 'wpshadow' ),
					__( 'Avoid creating custom globals in theme/plugin code', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Run this diagnostic after refactoring', 'wpshadow' ),
						__( 'Search codebase for "global $custom" declarations', 'wpshadow' ),
						__( 'Test functionality after removing globals', 'wpshadow' ),
						__( 'Run PHP_CodeSniffer for WordPress standards', 'wpshadow' ),
					),
					'expected_result' => __( '<3 custom global variables in codebase', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze global variable usage in theme and plugin files.
	 *
	 * @since  1.6028.1715
	 * @return array Analysis results with counts and examples.
	 */
	private static function analyze_global_variables() {
		$result = array(
			'custom_globals'      => 0,
			'total_declarations'  => 0,
			'files_affected'      => 0,
			'examples'            => array(),
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

		// Known WordPress globals to exclude.
		$wp_globals = array(
			'wpdb', 'post', 'wp_query', 'wp_rewrite', 'wp', 'wp_the_query',
			'wp_scripts', 'wp_styles', 'wp_filter', 'wp_admin_bar',
			'pagenow', 'typenow', 'current_screen', 'menu', 'submenu',
		);

		$global_pattern = '/global\s+\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
		$example_limit  = 10;

		foreach ( $files as $file ) {
			$content = @file_get_contents( $file );
			if ( $content === false ) {
				continue;
			}

			preg_match_all( $global_pattern, $content, $matches );

			if ( ! empty( $matches[1] ) ) {
				$file_has_custom_global = false;

				foreach ( $matches[1] as $var_name ) {
					$result['total_declarations']++;

					// Skip WordPress core globals.
					if ( in_array( $var_name, $wp_globals, true ) ) {
						continue;
					}

					$result['custom_globals']++;
					$file_has_custom_global = true;

					if ( count( $result['examples'] ) < $example_limit ) {
						$result['examples'][] = array(
							'variable' => '$' . $var_name,
							'file'     => str_replace( ABSPATH, '', $file ),
						);
					}
				}

				if ( $file_has_custom_global ) {
					$result['files_affected']++;
				}
			}
		}

		return $result;
	}

	/**
	 * Get all PHP files in a directory recursively.
	 *
	 * @since  1.6028.1715
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
