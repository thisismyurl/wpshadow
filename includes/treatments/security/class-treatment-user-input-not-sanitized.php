<?php
/**
 * User Input Not Sanitized Treatment
 *
 * Checks if theme and plugin code properly sanitizes user input
 * before saving to the database using WordPress sanitization functions.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6036.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Input Not Sanitized Treatment Class
 *
 * Detects potentially unsanitized user input that could lead
 * to XSS or SQL injection vulnerabilities. Checks theme and
 * custom plugin files for common patterns of unsafe input handling.
 *
 * @since 1.6036.1200
 */
class Treatment_User_Input_Not_Sanitized extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-input-not-sanitized';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'User Input Not Sanitized Before Saving';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form inputs are sanitized before storing';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6036.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = get_template_directory();
		$issues    = array();

		// Scan theme files for unsanitized input patterns
		$theme_files = self::get_php_files( $theme_dir );
		$issues      = self::scan_files_for_unsanitized_input( $theme_files, 'theme' );

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
					$plugin_issues = array_merge( $plugin_issues, self::scan_files_for_unsanitized_input( $plugin_php_files, 'plugin: ' . $plugin_name ) );
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
					__( 'Found %d potential unsanitized input instances. Raw user input can contain malicious code. Always sanitize with sanitize_text_field(), sanitize_email(), esc_url_raw(), or similar functions.', 'wpshadow' ),
					$issue_count
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/input-sanitization',
				'meta'         => array(
					'issue_count' => $issue_count,
					'examples'    => $examples,
					'guidance'    => array(
						'For text fields: sanitize_text_field( wp_unslash( $_POST[\'field\'] ) )',
						'For emails: sanitize_email( wp_unslash( $_POST[\'email\'] ) )',
						'For URLs: esc_url_raw( wp_unslash( $_POST[\'url\'] ) )',
						'For textareas: sanitize_textarea_field( wp_unslash( $_POST[\'message\'] ) )',
						'For keys: sanitize_key( $_POST[\'key\'] )',
						'For integers: absint( $_POST[\'number\'] )',
					),
				),
			);
		}

		return null;
	}

	/**
	 * Get all PHP files in a directory recursively.
	 *
	 * @since  1.6036.1200
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
	 * Scan files for unsanitized input patterns.
	 *
	 * @since  1.6036.1200
	 * @param  array  $files Array of file paths.
	 * @param  string $source Source identifier (theme/plugin name).
	 * @return array Array of issues found.
	 */
	private static function scan_files_for_unsanitized_input( array $files, string $source ): array {
		$issues = array();

		foreach ( $files as $file ) {
			$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( false === $content ) {
				continue;
			}

			// Common patterns of unsanitized input being saved
			$patterns = array(
				'/update_option\([^,]+,\s*\$_(GET|POST|REQUEST|COOKIE)\[/'                => 'Direct save of superglobal to option',
				'/update_post_meta\([^,]+,[^,]+,\s*\$_(GET|POST|REQUEST)\[/'              => 'Direct save of superglobal to post meta',
				'/\$wpdb->(?:insert|update|replace)\([^;]+\$_(GET|POST|REQUEST)\[/'       => 'Direct use of superglobal in database query',
				'/set_transient\([^,]+,\s*\$_(GET|POST|REQUEST)\[(?!.*sanitize)/'         => 'Direct save of superglobal to transient',
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
	 * @since  1.6036.1200
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
