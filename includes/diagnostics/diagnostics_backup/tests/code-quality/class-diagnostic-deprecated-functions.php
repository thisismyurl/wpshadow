<?php
/**
 * Deprecated WordPress Functions Diagnostic
 *
 * Detects usage of deprecated WordPress functions that should be replaced
 * with modern alternatives for compatibility and security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1750
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Deprecated_Functions Class
 *
 * Scans PHP files for deprecated WordPress functions and suggests replacements.
 *
 * @since 1.6028.1750
 */
class Diagnostic_Deprecated_Functions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'deprecated-functions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Deprecated WordPress Functions Used';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects deprecated WordPress functions that need updating';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code_quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1750
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$deprecated_usage = self::scan_for_deprecated_functions();

		if ( empty( $deprecated_usage ) ) {
			return null; // No deprecated functions found.
		}

		$deprecated_count = count( $deprecated_usage );

		// Determine severity based on count and age.
		$has_critical = false;
		foreach ( $deprecated_usage as $usage ) {
			if ( $usage['deprecated_since'] < 5.0 ) {
				$has_critical = true;
				break;
			}
		}

		if ( $has_critical ) {
			$severity     = 'medium';
			$threat_level = 60;
		} elseif ( $deprecated_count > 10 ) {
			$severity     = 'medium';
			$threat_level = 50;
		} else {
			$severity     = 'low';
			$threat_level = 35;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: count of deprecated functions */
				_n(
					'Found %d deprecated WordPress function',
					'Found %d deprecated WordPress functions',
					$deprecated_count,
					'wpshadow'
				),
				$deprecated_count
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/deprecated-functions',
			'family'      => self::$family,
			'meta'        => array(
				'deprecated_count' => $deprecated_count,
				'critical_count'   => $has_critical ? 1 : 0,
				'recommended'      => __( 'Update to modern WordPress function alternatives', 'wpshadow' ),
				'impact_level'     => 'medium',
				'immediate_actions' => array(
					__( 'Review list of deprecated functions', 'wpshadow' ),
					__( 'Replace with modern alternatives', 'wpshadow' ),
					__( 'Test after each replacement', 'wpshadow' ),
					__( 'Enable WP_DEBUG to catch more', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Deprecated functions are removed in future WordPress versions. Using them causes compatibility breaks, security vulnerabilities, and prevents WordPress updates. Modern alternatives are faster, more secure, and better maintained. Sites with deprecated code become technical debt.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Site Breaks: Functions removed cause fatal errors', 'wpshadow' ),
					__( 'Security Risks: Old code has unpatched vulnerabilities', 'wpshadow' ),
					__( 'Can\'t Update WordPress: Forced to stay on old versions', 'wpshadow' ),
					__( 'Performance Issues: Old functions are slower', 'wpshadow' ),
				),
				'deprecated_functions' => array_slice( $deprecated_usage, 0, 10 ), // Limit to 10 for display.
				'severity_levels' => array(
					'critical' => __( 'Deprecated before WP 5.0 (2018)', 'wpshadow' ),
					'high'     => __( 'Deprecated in WP 5.x', 'wpshadow' ),
					'medium'   => __( 'Deprecated in WP 6.x', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Manual Code Review', 'wpshadow' ),
						'description' => __( 'Replace deprecated functions with alternatives', 'wpshadow' ),
						'steps'       => array(
							__( 'Enable WP_DEBUG and WP_DEBUG_LOG in wp-config.php', 'wpshadow' ),
							__( 'Visit all pages to trigger deprecation notices', 'wpshadow' ),
							__( 'Check debug.log for _deprecated_function notices', 'wpshadow' ),
							__( 'Look up replacement in WordPress documentation', 'wpshadow' ),
							__( 'Replace function and test thoroughly', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Query Monitor Plugin', 'wpshadow' ),
						'description' => __( 'Track deprecated notices in admin toolbar', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Query Monitor plugin (free)', 'wpshadow' ),
							__( 'Browse site logged in as admin', 'wpshadow' ),
							__( 'Click Query Monitor toolbar item', 'wpshadow' ),
							__( 'Check "Deprecated" tab for warnings', 'wpshadow' ),
							__( 'Follow stack trace to find usage location', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Automated Code Scanner', 'wpshadow' ),
						'description' => __( 'Use PHPCompatibilityWP to scan codebase', 'wpshadow' ),
						'steps'       => array(
							__( 'Install PHP_CodeSniffer via Composer', 'wpshadow' ),
							__( 'Install PHPCompatibilityWP standard', 'wpshadow' ),
							__( 'Run: phpcs --standard=PHPCompatibilityWP path/', 'wpshadow' ),
							__( 'Review report for deprecated functions', 'wpshadow' ),
							__( 'Replace and re-scan until clean', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Always test on staging before production', 'wpshadow' ),
					__( 'Replace one function at a time', 'wpshadow' ),
					__( 'Keep backups before making changes', 'wpshadow' ),
					__( 'Search entire codebase for each function', 'wpshadow' ),
					__( 'Update plugins/themes instead of core hacks', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Enable WP_DEBUG and WP_DEBUG_LOG', 'wpshadow' ),
						__( 'Browse all pages of site', 'wpshadow' ),
						__( 'Check wp-content/debug.log for notices', 'wpshadow' ),
						__( 'Run this diagnostic again after fixes', 'wpshadow' ),
					),
					'expected_result' => __( 'No deprecated function warnings in logs', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Scan codebase for deprecated WordPress functions.
	 *
	 * @since  1.6028.1750
	 * @return array Deprecated function usage details.
	 */
	private static function scan_for_deprecated_functions() {
		$deprecated_map = self::get_deprecated_functions_map();
		$found = array();

		// Scan theme files.
		$theme_files = self::get_php_files( get_template_directory() );
		foreach ( $theme_files as $file ) {
			$usages = self::scan_file_for_functions( $file, $deprecated_map );
			$found = array_merge( $found, $usages );
		}

		// Scan active plugins (limit to 5 for performance).
		$active_plugins = array_slice( get_option( 'active_plugins', array() ), 0, 5 );
		foreach ( $active_plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
			if ( is_dir( $plugin_dir ) ) {
				$plugin_files = self::get_php_files( $plugin_dir );
				foreach ( $plugin_files as $file ) {
					$usages = self::scan_file_for_functions( $file, $deprecated_map );
					$found = array_merge( $found, $usages );
				}
			}
		}

		return array_slice( $found, 0, 50 ); // Limit to 50 results.
	}

	/**
	 * Get map of deprecated functions and their replacements.
	 *
	 * @since  1.6028.1750
	 * @return array Deprecated function map.
	 */
	private static function get_deprecated_functions_map() {
		return array(
			// Critical (removed or very old).
			'get_settings'         => array( 'replacement' => 'get_option()', 'since' => 2.1 ),
			'wp_setcookie'         => array( 'replacement' => 'wp_set_auth_cookie()', 'since' => 2.5 ),
			'get_category_children' => array( 'replacement' => 'get_term_children()', 'since' => 2.8 ),
			'get_bloginfo_rss'     => array( 'replacement' => 'get_bloginfo()', 'since' => 2.2 ),
			
			// High priority (WP 5.x).
			'get_plugin_data'      => array( 'replacement' => 'Use WP_Theme or get_file_data()', 'since' => 4.9 ),
			'create_function'      => array( 'replacement' => 'Anonymous functions', 'since' => 7.2 ),
			'_wp_render_title_tag' => array( 'replacement' => 'wp_get_document_title()', 'since' => 5.0 ),
			
			// Medium priority (WP 6.x).
			'wp_get_attachment_thumb_file' => array( 'replacement' => 'wp_get_attachment_image_src()', 'since' => 5.3 ),
			'get_category_link'    => array( 'replacement' => 'get_term_link()', 'since' => 2.5 ),
			'like_escape'          => array( 'replacement' => 'wpdb::esc_like()', 'since' => 4.0 ),
			'has_cap'              => array( 'replacement' => 'current_user_can()', 'since' => 3.0 ),
			'user_pass_ok'         => array( 'replacement' => 'wp_authenticate()', 'since' => 2.8 ),
			
			// Formatting functions.
			'attribute_escape'     => array( 'replacement' => 'esc_attr()', 'since' => 2.8 ),
			'clean_url'            => array( 'replacement' => 'esc_url()', 'since' => 3.0 ),
			'sanitize_url'         => array( 'replacement' => 'esc_url_raw()', 'since' => 2.8 ),
			'js_escape'            => array( 'replacement' => 'esc_js()', 'since' => 2.8 ),
			
			// Taxonomy functions.
			'get_catname'          => array( 'replacement' => 'get_cat_name()', 'since' => 2.1 ),
			'get_category_parents' => array( 'replacement' => 'get_term_parents_list()', 'since' => 5.3 ),
			'is_taxonomy'          => array( 'replacement' => 'taxonomy_exists()', 'since' => 3.0 ),
			
			// User functions.
			'get_userdatabylogin'  => array( 'replacement' => 'get_user_by()', 'since' => 3.3 ),
			'get_user_id_from_string' => array( 'replacement' => 'get_user_by()', 'since' => 3.6 ),
			'set_current_user'     => array( 'replacement' => 'wp_set_current_user()', 'since' => 3.0 ),
		);
	}

	/**
	 * Scan single file for deprecated functions.
	 *
	 * @since  1.6028.1750
	 * @param  string $file Path to file.
	 * @param  array  $deprecated_map Map of deprecated functions.
	 * @return array Found deprecated usages.
	 */
	private static function scan_file_for_functions( $file, $deprecated_map ) {
		$found = array();
		$content = @file_get_contents( $file );
		
		if ( $content === false ) {
			return $found;
		}

		foreach ( $deprecated_map as $function => $info ) {
			// Match function calls (avoid matching in comments).
			$pattern = '/\b' . preg_quote( $function, '/' ) . '\s*\(/';
			
			if ( preg_match( $pattern, $content, $matches, PREG_OFFSET_CAPTURE ) ) {
				// Get line number.
				$line_number = substr_count( substr( $content, 0, $matches[0][1] ), "\n" ) + 1;
				
				// Get code context.
				$lines = explode( "\n", $content );
				$context_line = $lines[ $line_number - 1 ] ?? '';

				$found[] = array(
					'function'          => $function,
					'file'              => str_replace( ABSPATH, '', $file ),
					'line'              => $line_number,
					'context'           => trim( $context_line ),
					'replacement'       => $info['replacement'],
					'deprecated_since'  => $info['since'],
				);
			}
		}

		return $found;
	}

	/**
	 * Get all PHP files in a directory recursively.
	 *
	 * @since  1.6028.1750
	 * @param  string $directory Directory path.
	 * @return array PHP file paths.
	 */
	private static function get_php_files( $directory ) {
		$files = array();
		
		if ( ! is_dir( $directory ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS ),
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

			// Limit to 50 files per directory for performance.
			if ( count( $files ) >= 50 ) {
				break;
			}
		}

		return $files;
	}
}
