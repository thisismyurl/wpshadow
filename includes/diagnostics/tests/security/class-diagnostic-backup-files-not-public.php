<?php
/**
 * Backup Files Not Public Diagnostic
 *
 * Searches shallow web-accessible directories for archive and database files
 * that could expose sensitive data if reachable via a browser.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Diagnostics;

use ThisIsMyURL\Shadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Backup_Files_Not_Public Class
 *
 * @since 0.6095
 */
class Diagnostic_Backup_Files_Not_Public extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'backup-files-not-public';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Backup Files Not Public';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Searches web-accessible directories for archive and database dump files that could allow an attacker to download your site database or source code.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans ABSPATH and WP_CONTENT_DIR (non-recursively) for files with backup
	 * extensions or backup-related names. Does not scan recursively to keep the
	 * check fast and avoid re-scanning managed backup plugin storage.
	 *
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$scan_dirs = array(
			rtrim( ABSPATH, '/' ),
			WP_CONTENT_DIR,
		);

		$dangerous_extensions = array( '.zip', '.tar', '.tar.gz', '.tgz', '.gz', '.sql', '.bak', '.backup', '.dump' );
		$backup_name_patterns = array( 'backup', 'dump', 'archive', 'export', 'migrate' );

		$found = array();

		foreach ( $scan_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}
			$files = @scandir( $dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( ! $files ) {
				continue;
			}
			foreach ( $files as $file ) {
				if ( '.' === $file || '..' === $file ) {
					continue;
				}
				$full_path = $dir . '/' . $file;
				if ( ! is_file( $full_path ) ) {
					continue;
				}
				$lower = strtolower( $file );

				// Check extension.
				$ext_match = false;
				foreach ( $dangerous_extensions as $ext ) {
					if ( substr( $lower, -strlen( $ext ) ) === $ext ) {
						$ext_match = true;
						break;
					}
				}

				// Check name pattern.
				$name_match = false;
				foreach ( $backup_name_patterns as $pattern ) {
					if ( false !== strpos( $lower, $pattern ) ) {
						$name_match = true;
						break;
					}
				}

				if ( $ext_match || $name_match ) {
					$found[] = str_replace( rtrim( ABSPATH, '/' ) . '/', '', $full_path );
				}
			}
		}

		if ( empty( $found ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: list of found backup files */
				__( 'Potential backup or archive files were found in publicly accessible directories: %s. If these files are reachable via a web browser, they may allow an attacker to download your site\'s database, source code, or configuration files. Move backups outside the webroot or block access via server rules.', 'thisismyurl-shadow' ),
				implode( ', ', array_slice( $found, 0, 5 ) ) . ( count( $found ) > 5 ? ' (and more)' : '' )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'details'      => array(
				'found_files' => $found,
			),
		);
	}
}
