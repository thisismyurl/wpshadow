<?php
/**
 * User Input Not Sanitized Treatment
 *
 * Checks if theme and plugin code properly sanitizes user input
 * before saving to the database using WordPress sanitization functions.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
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
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_User_Input_Not_Sanitized' );
	}

	/**
	 * Get all PHP files in a directory recursively.
	 *
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
