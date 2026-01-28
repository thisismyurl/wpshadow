<?php
/**
 * Inline JavaScript in Templates Diagnostic
 *
 * Detects inline <script> tags in template files. JavaScript should be
 * enqueued properly via wp_enqueue_script() for better performance,
 * caching, and CSP compatibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Inline_JavaScript Class
 *
 * Scans template files for inline <script> tags that should be enqueued.
 *
 * @since 1.6028.1800
 */
class Diagnostic_Inline_JavaScript extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inline-javascript';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inline JavaScript in Templates';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects inline script tags that should be properly enqueued';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code_quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$inline_scripts = self::scan_for_inline_scripts();

		if ( empty( $inline_scripts ) ) {
			return null; // No inline scripts found.
		}

		$script_count = count( $inline_scripts );

		// Calculate total lines of inline JS.
		$total_lines = 0;
		foreach ( $inline_scripts as $script ) {
			$total_lines += $script['line_count'];
		}

		// Determine severity based on count and size.
		if ( $total_lines > 100 || $script_count > 10 ) {
			$severity     = 'medium';
			$threat_level = 50;
		} elseif ( $total_lines > 50 || $script_count > 5 ) {
			$severity     = 'low';
			$threat_level = 40;
		} else {
			$severity     = 'low';
			$threat_level = 30;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: count of inline scripts, 2: total lines */
				__( 'Found %1$d inline script tags (%2$d lines total)', 'wpshadow' ),
				$script_count,
				$total_lines
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/inline-javascript',
			'family'      => self::$family,
			'meta'        => array(
				'script_count'      => $script_count,
				'total_lines'       => $total_lines,
				'recommended'       => __( 'Move JavaScript to external files and enqueue via wp_enqueue_script()', 'wpshadow' ),
				'impact_level'      => 'medium',
				'immediate_actions' => array(
					__( 'Extract inline scripts to .js files', 'wpshadow' ),
					__( 'Enqueue via wp_enqueue_script()', 'wpshadow' ),
					__( 'Use wp_add_inline_script() for critical JS', 'wpshadow' ),
					__( 'Test functionality after migration', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Inline JavaScript in templates hurts performance (no caching, no minification, no async loading), breaks Content Security Policy (CSP), causes code duplication, and makes debugging harder. WordPress provides wp_enqueue_script() for proper script management. Professional themes/plugins never put JavaScript directly in templates except for critical tiny snippets via wp_add_inline_script().', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Slower Page Load: Scripts can\'t be cached or deferred', 'wpshadow' ),
					__( 'Repeated Downloads: Same code downloaded on every page', 'wpshadow' ),
					__( 'Breaks CSP: Security policies block inline scripts', 'wpshadow' ),
					__( 'Hard to Debug: No source maps or clear file structure', 'wpshadow' ),
					__( 'Can\'t Minify: Inline code bypasses optimization tools', 'wpshadow' ),
				),
				'inline_scripts' => array_slice( $inline_scripts, 0, 10 ), // Limit to 10 for display.
				'acceptable_exceptions' => array(
					__( 'Google Analytics snippets (vendor requirement)', 'wpshadow' ),
					__( 'Critical above-the-fold scripts (use wp_add_inline_script)', 'wpshadow' ),
					__( 'Dynamic data injection (use wp_localize_script instead)', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Extract to External File', 'wpshadow' ),
						'description' => __( 'Move inline JavaScript to proper .js file', 'wpshadow' ),
						'steps'       => array(
							__( 'Copy inline JavaScript from template', 'wpshadow' ),
							__( 'Create new file: assets/js/my-script.js', 'wpshadow' ),
							__( 'Paste code into new file', 'wpshadow' ),
							__( 'Enqueue: wp_enqueue_script(\'my-script\', get_template_directory_uri() . \'/assets/js/my-script.js\', array(\'jquery\'), \'1.0\', true)', 'wpshadow' ),
							__( 'Remove inline <script> tag from template', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Use wp_add_inline_script()', 'wpshadow' ),
						'description' => __( 'For small critical scripts that must be inline', 'wpshadow' ),
						'steps'       => array(
							__( 'Enqueue main script: wp_enqueue_script(\'my-script\', ...)', 'wpshadow' ),
							__( 'Add inline code: wp_add_inline_script(\'my-script\', \'// Your JS here\', \'after\')', 'wpshadow' ),
							__( 'This prints script after main file loads', 'wpshadow' ),
							__( 'Remove <script> tag from template', 'wpshadow' ),
							__( 'Test to ensure execution order correct', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Conditional Enqueue System', 'wpshadow' ),
						'description' => __( 'Load scripts only where needed', 'wpshadow' ),
						'steps'       => array(
							__( 'Create enqueue function in functions.php', 'wpshadow' ),
							__( 'Use conditional logic: if (is_front_page()) { wp_enqueue_script(...) }', 'wpshadow' ),
							__( 'For page-specific: if (is_page(\'contact\')) { enqueue }', 'wpshadow' ),
							__( 'Extract all inline scripts to separate files', 'wpshadow' ),
							__( 'Test each page loads only needed scripts', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Never use inline <script> tags in templates', 'wpshadow' ),
					__( 'Always enqueue via wp_enqueue_script()', 'wpshadow' ),
					__( 'Load scripts in footer (5th parameter = true)', 'wpshadow' ),
					__( 'Declare dependencies properly (array param)', 'wpshadow' ),
					__( 'Use wp_localize_script() for PHP-to-JS data', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'View page source after changes', 'wpshadow' ),
						__( 'Verify no <script> tags in body content', 'wpshadow' ),
						__( 'Check all scripts load from external files', 'wpshadow' ),
						__( 'Test JavaScript functionality works', 'wpshadow' ),
						__( 'Run PageSpeed Insights to confirm caching', 'wpshadow' ),
					),
					'expected_result' => __( 'No inline <script> tags in templates (except tiny critical snippets)', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Scan template files for inline script tags.
	 *
	 * @since  1.6028.1800
	 * @return array Inline script details.
	 */
	private static function scan_for_inline_scripts() {
		$found = array();

		// Scan theme template files.
		$theme_dir = get_template_directory();
		$theme_files = self::get_template_files( $theme_dir );
		
		foreach ( $theme_files as $file ) {
			$scripts = self::scan_file_for_script_tags( $file );
			$found = array_merge( $found, $scripts );
		}

		// Scan active plugins (limit to 3 for performance).
		$active_plugins = array_slice( get_option( 'active_plugins', array() ), 0, 3 );
		foreach ( $active_plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
			if ( is_dir( $plugin_dir ) ) {
				$plugin_files = self::get_template_files( $plugin_dir );
				foreach ( $plugin_files as $file ) {
					$scripts = self::scan_file_for_script_tags( $file );
					$found = array_merge( $found, $scripts );
				}
			}
		}

		return array_slice( $found, 0, 50 ); // Limit to 50 results.
	}

	/**
	 * Scan single file for inline script tags.
	 *
	 * @since  1.6028.1800
	 * @param  string $file Path to file.
	 * @return array Found inline scripts.
	 */
	private static function scan_file_for_script_tags( $file ) {
		$found = array();
		$content = @file_get_contents( $file );
		
		if ( $content === false ) {
			return $found;
		}

		// Match <script> tags that don't have src attribute (inline scripts).
		$pattern = '/<script(?![^>]*\ssrc=)[^>]*>(.*?)<\/script>/is';
		
		if ( preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$full_tag = $match[0][0];
				$script_content = $match[1][0];
				$position = $match[0][1];
				
				// Skip if it's just whitespace or a very short snippet.
				if ( strlen( trim( $script_content ) ) < 10 ) {
					continue;
				}

				// Skip if it's just JSON data (common pattern).
				if ( strpos( $full_tag, 'type="application/json"' ) !== false ||
				     strpos( $full_tag, 'type="application/ld+json"' ) !== false ) {
					continue;
				}

				// Get line number.
				$line_number = substr_count( substr( $content, 0, $position ), "\n" ) + 1;
				
				// Count lines of JavaScript.
				$js_line_count = substr_count( $script_content, "\n" ) + 1;

				// Get preview of script content.
				$preview = trim( substr( $script_content, 0, 100 ) );
				if ( strlen( $script_content ) > 100 ) {
					$preview .= '...';
				}

				$found[] = array(
					'file'       => str_replace( ABSPATH, '', $file ),
					'line'       => $line_number,
					'line_count' => $js_line_count,
					'preview'    => $preview,
					'size_bytes' => strlen( $script_content ),
				);
			}
		}

		return $found;
	}

	/**
	 * Get template files recursively.
	 *
	 * @since  1.6028.1800
	 * @param  string $directory Directory path.
	 * @return array Template file paths.
	 */
	private static function get_template_files( $directory ) {
		$files = array();
		
		if ( ! is_dir( $directory ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$ext = $file->getExtension();
				// Check PHP, HTML, and template files.
				if ( in_array( $ext, array( 'php', 'html', 'tpl', 'phtml' ), true ) ) {
					// Skip vendor and minified files.
					if ( strpos( $file->getPathname(), '/vendor/' ) !== false ||
					     strpos( $file->getPathname(), '/node_modules/' ) !== false ) {
						continue;
					}
					$files[] = $file->getPathname();
				}
			}

			// Limit to 50 files per directory for performance.
			if ( count( $files ) >= 50 ) {
				break;
			}
		}

		return $files;
	}
}
