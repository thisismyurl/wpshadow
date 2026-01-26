<?php
/**
 * Diagnostic: WP Content Directory Security
 *
 * Detects if visitors can execute code in /wp-content/ directory or subdirectories.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_WP_Content_Directory_Secure
 *
 * Checks if PHP execution is possible in wp-content directories (especially uploads),
 * which would allow attackers to execute malicious code if they can upload files.
 *
 * @since 1.2601.2148
 */
class Diagnostic_WP_Content_Directory_Secure extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-content-directory-secure';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WP Content Directory Security';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if visitors can execute code in /wp-content/ directory or subdirectories';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for .htaccess or other protection preventing PHP execution
	 * in wp-content/uploads and other sensitive directories.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if vulnerable, null otherwise.
	 */
	public static function check() {
		$vulnerable_dirs = array();
		$upload_dir = wp_upload_dir();

		// Check uploads directory
		$uploads_path = $upload_dir['basedir'];
		if ( ! self::is_directory_protected( $uploads_path ) ) {
			$vulnerable_dirs[] = array(
				'path' => $uploads_path,
				'name' => __( 'Uploads directory', 'wpshadow' ),
			);
		}

		// Check plugins directory
		$plugins_path = WP_PLUGIN_DIR;
		if ( ! self::is_directory_protected( $plugins_path ) ) {
			$vulnerable_dirs[] = array(
				'path' => $plugins_path,
				'name' => __( 'Plugins directory', 'wpshadow' ),
			);
		}

		// Check themes directory
		$themes_path = get_theme_root();
		if ( ! self::is_directory_protected( $themes_path ) ) {
			$vulnerable_dirs[] = array(
				'path' => $themes_path,
				'name' => __( 'Themes directory', 'wpshadow' ),
			);
		}

		if ( empty( $vulnerable_dirs ) ) {
			return null;
		}

		$vuln_count = count( $vulnerable_dirs );
		$dir_names = array_column( $vulnerable_dirs, 'name' );
		
		$description = sprintf(
			/* translators: %d: number of vulnerable directories */
			_n(
				'Found %d directory in wp-content where PHP execution may be possible. If an attacker can upload files, they could execute malicious code.',
				'Found %d directories in wp-content where PHP execution may be possible. If an attacker can upload files, they could execute malicious code.',
				$vuln_count,
				'wpshadow'
			),
			$vuln_count
		) . ' ' . sprintf(
			/* translators: %s: comma-separated list of directory names */
			__( 'Vulnerable directories: %s', 'wpshadow' ),
			esc_html( implode( ', ', $dir_names ) )
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'medium',
			'threat_level' => 50,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/security-wp-content-directory-secure',
			'meta'        => array(
				'vulnerable_directories' => $vulnerable_dirs,
				'vuln_count' => $vuln_count,
			),
		);
	}

	/**
	 * Check if a directory is protected from PHP execution.
	 *
	 * Looks for .htaccess or other protection mechanisms.
	 *
	 * @since  1.2601.2148
	 * @param  string $directory_path Path to directory.
	 * @return bool True if protected, false otherwise.
	 */
	private static function is_directory_protected( string $directory_path ): bool {
		if ( ! is_dir( $directory_path ) ) {
			return true; // Directory doesn't exist, not vulnerable
		}

		// Check for .htaccess with PHP denial rules
		$htaccess_file = trailingslashit( $directory_path ) . '.htaccess';
		
		if ( file_exists( $htaccess_file ) && is_readable( $htaccess_file ) ) {
			$htaccess_content = file_get_contents( $htaccess_file );
			
			if ( false !== $htaccess_content ) {
				// Look for common PHP blocking patterns
				$blocking_patterns = array(
					'php_flag engine off',
					'RemoveHandler .php',
					'RemoveType .php',
					'<Files *.php>',
					'deny from all',
					'Require all denied',
				);

				foreach ( $blocking_patterns as $pattern ) {
					if ( false !== stripos( $htaccess_content, $pattern ) ) {
						return true; // Protection found
					}
				}
			}
		}

		// Check for index.php (WordPress standard protection)
		$index_file = trailingslashit( $directory_path ) . 'index.php';
		if ( file_exists( $index_file ) ) {
			// Index.php exists, provides some protection (directory listing prevention)
			// But doesn't prevent PHP execution, so we still consider it vulnerable
		}

		return false; // No protection found
	}
}
