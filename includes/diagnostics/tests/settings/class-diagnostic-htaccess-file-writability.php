<?php
/**
 * htaccess File Writability Diagnostic
 *
 * Verifies the .htaccess file has correct permissions and can be updated by WordPress.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * htaccess File Writability Diagnostic Class
 *
 * Checks .htaccess file permissions and write accessibility.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Htaccess_File_Writability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'htaccess-file-writability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'htaccess File Writability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies .htaccess file permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'server';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for Apache servers.
		if ( ! function_exists( 'got_mod_rewrite' ) || ! got_mod_rewrite() ) {
			return null;
		}

		$issues        = array();
		$htaccess_file = get_home_path() . '.htaccess';

		// Check if .htaccess exists.
		if ( ! file_exists( $htaccess_file ) ) {
			// Check if directory is writable to create .htaccess.
			$home_path = get_home_path();
			if ( ! is_writable( $home_path ) ) {
				$issues[] = __( '.htaccess file does not exist and directory is not writable', 'wpshadow' );
			} else {
				// File doesn't exist but can be created - not an issue if permalinks are default.
				$permalink_structure = get_option( 'permalink_structure' );
				if ( ! empty( $permalink_structure ) ) {
					$issues[] = __( '.htaccess file is missing (required for custom permalinks)', 'wpshadow' );
				}
			}
		} else {
			// File exists - check writability.
			if ( ! is_writable( $htaccess_file ) ) {
				$issues[] = __( '.htaccess file exists but is not writable', 'wpshadow' );
			}

			// Check file permissions.
			$perms = fileperms( $htaccess_file );
			$perms = substr( sprintf( '%o', $perms ), -4 );

			// Recommend 644 or 664.
			if ( '0644' !== $perms && '0664' !== $perms ) {
				$issues[] = sprintf(
					/* translators: %s: file permissions */
					__( '.htaccess has non-standard permissions (%s)', 'wpshadow' ),
					$perms
				);
			}

			// Check if file is too large.
			$filesize = filesize( $htaccess_file );
			if ( $filesize > 102400 ) { // 100KB.
				$issues[] = sprintf(
					/* translators: %s: file size */
					__( '.htaccess file is unusually large (%s)', 'wpshadow' ),
					size_format( $filesize )
				);
			}

			// Check for WordPress markers.
			$htaccess_content = file_get_contents( $htaccess_file );
			if ( false === strpos( $htaccess_content, '# BEGIN WordPress' ) ) {
				$permalink_structure = get_option( 'permalink_structure' );
				if ( ! empty( $permalink_structure ) ) {
					$issues[] = __( '.htaccess missing WordPress rewrite rules', 'wpshadow' );
				}
			}

			// Check for duplicate WordPress blocks.
			$wp_block_count = substr_count( $htaccess_content, '# BEGIN WordPress' );
			if ( $wp_block_count > 1 ) {
				$issues[] = __( 'Multiple WordPress rewrite rule blocks found in .htaccess', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/htaccess-file-writability',
			);
		}

		return null;
	}
}
