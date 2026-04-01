<?php
/**
 * Output Not Escaped Diagnostic
 *
 * Checks if theme and plugin code properly escapes output using
 * context-appropriate WordPress functions (esc_html, esc_attr, esc_url, etc).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output Not Escaped Diagnostic Class
 *
 * Detects potentially unescaped output that could lead to XSS
 * vulnerabilities. Checks theme and custom plugin files for
 * common patterns of unsafe output.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Output_Not_Escaped extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'output-not-escaped';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Output Not Escaped for Display Context';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if output is properly escaped with context-appropriate functions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = get_template_directory();
		$issues    = array();

		// Scan theme files for unescaped output patterns
		$theme_files = self::get_php_files( $theme_dir );
		$issues      = self::scan_files_for_unescaped_output( $theme_files, 'theme' );

		// Scan custom plugins (skip major plugin vendors)
		$plugin_dir    = WP_PLUGIN_DIR;
		$plugin_issues = array();

		if ( is_dir( $plugin_dir ) ) {
			$plugins = array_filter( glob( $plugin_dir . '/*' ), 'is_dir' );
			foreach ( $plugins as $plugin_path ) {
				$plugin_name = basename( $plugin_path );

				// Skip well-known plugins to avoid false positives
				$skip_plugins = array( 'woocommerce', 'elementor', 'wordpress-seo', 'jetpack', 'akismet' );
				if ( in_array( $plugin_name, $skip_plugins, true ) ) {
					continue;
				}

				// Scan small custom plugins only (< 50 files)
				$plugin_php_files = self::get_php_files( $plugin_path );
				if ( count( $plugin_php_files ) < 50 ) {
					$plugin_issues = array_merge( $plugin_issues, self::scan_files_for_unescaped_output( $plugin_php_files, 'plugin: ' . $plugin_name ) );
				}
			}
		}

		$all_issues = array_merge( $issues, $plugin_issues );

		if ( ! empty( $all_issues ) ) {
			$issue_count = count( $all_issues );
			$examples    = array_slice( $all_issues, 0, 3 );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of potential issues */
					__( 'Found %d potential unescaped output instances. Unescaped output allows XSS attacks. Use esc_html(), esc_attr(), esc_url(), or wp_kses_post() based on context.', 'wpshadow' ),
					$issue_count
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/output-escaping?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'issue_count' => $issue_count,
					'examples'    => $examples,
					'guidance'    => array(
						'For HTML content: esc_html( $variable )',
						'For HTML attributes: esc_attr( $variable )',
						'For URLs: esc_url( $url )',
						'For JavaScript: esc_js( $string )',
						'For safe HTML: wp_kses_post( $html )',
					),
				),
			);
		}

		return null;
	}

	/**
	 * Get all PHP files in a directory recursively.
	 *
	 * @since 0.6093.1200
	 * @param  string $dir Directory path.
	 * @return array Array of file paths.
	 */
	private static function get_php_files( string $dir ): array {
		$files = array();

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				$files[] = $file->getPathname();
			}

			// Limit to 100 files per directory for performance
			if ( count( $files ) >= 100 ) {
				break;
			}
		}

		return $files;
	}

	/**
	 * Scan files for unescaped output patterns.
	 *
	 * @since 0.6093.1200
	 * @param  array  $files Array of file paths.
	 * @param  string $source Source identifier (theme/plugin name).
	 * @return array Array of issues found.
	 */
	private static function scan_files_for_unescaped_output( array $files, string $source ): array {
		$issues = array();

		foreach ( $files as $file ) {
			$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( false === $content ) {
				continue;
			}

			// Common patterns of unescaped output
			$patterns = array(
				'/echo\s+\$_(GET|POST|REQUEST|COOKIE)\[/' => 'Direct echo of superglobal',
				'/echo\s+get_option\([^)]+\)[^;]*;(?!.*esc_)/' => 'Unescaped get_option',
				'/echo\s+\$[a-zA-Z_]+\s*;(?=.*\?>)/'      => 'Echo variable without escaping near PHP close tag',
				'/<[a-z]+\s+[^>]*="\s*<?php\s+echo\s+\$[^;]+;?\s*\?>.*">/' => 'Direct echo in HTML attribute',
			);

			foreach ( $patterns as $pattern => $description ) {
				if ( preg_match( $pattern, $content ) ) {
					$issues[] = array(
						'file'        => str_replace( ABSPATH, '', $file ),
						'source'      => $source,
						'pattern'     => $description,
						'line_sample' => self::get_matching_line( $content, $pattern ),
					);

					// Limit issues per file
					if ( count( $issues ) >= 20 ) {
						break 2;
					}
					break;
				}
			}
		}

		return $issues;
	}

	/**
	 * Get a sample line matching the pattern.
	 *
	 * @since 0.6093.1200
	 * @param  string $content File content.
	 * @param  string $pattern Regex pattern.
	 * @return string Sample line or empty string.
	 */
	private static function get_matching_line( string $content, string $pattern ): string {
		$lines = explode( "\n", $content );
		foreach ( $lines as $line ) {
			if ( preg_match( $pattern, $line ) ) {
				return trim( substr( $line, 0, 100 ) ) . '...';
			}
		}

		return '';
	}
}
