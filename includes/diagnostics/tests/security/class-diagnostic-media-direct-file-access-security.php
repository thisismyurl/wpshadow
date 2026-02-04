<?php
/**
 * Media Direct File Access Security Diagnostic
 *
 * Tests if direct access to PHP files in uploads is blocked.
 * Validates .htaccess rules prevent direct PHP execution.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.2100
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Direct_File_Access_Security Class
 *
 * Checks if direct PHP file execution is blocked in the uploads directory.
 * This is a critical security control that prevents attackers from executing
 * malicious PHP scripts if they manage to upload them.
 *
 * @since 1.6033.2100
 */
class Diagnostic_Media_Direct_File_Access_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-direct-file-access-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Direct File Access Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if direct PHP file execution is blocked in uploads directory';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$upload_dir = wp_upload_dir();
		$uploads_path = $upload_dir['basedir'];
		
		if ( empty( $uploads_path ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Could not determine uploads directory path.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-direct-file-access-security',
			);
		}

		// Check for .htaccess file
		$htaccess_path = $uploads_path . '/.htaccess';
		$htaccess_exists = file_exists( $htaccess_path );
		
		// Check for web.config file (IIS)
		$web_config_path = $uploads_path . '/web.config';
		$web_config_exists = file_exists( $web_config_path );

		// Check if running on Windows/IIS
		$is_windows = 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) );
		
		// Determine which file we should check
		if ( $is_windows ) {
			// On Windows/IIS, web.config is primary
			if ( ! $web_config_exists && ! $htaccess_exists ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'No .htaccess or web.config file found in uploads directory. Direct PHP file execution is not blocked.', 'wpshadow' ),
					'severity'      => 'critical',
					'threat_level'  => 80,
					'auto_fixable'  => true,
					'kb_link'       => 'https://wpshadow.com/kb/media-direct-file-access-security',
				);
			}
		} else {
			// On Unix/Apache, check .htaccess
			if ( ! $htaccess_exists ) {
				// Web server might not support .htaccess but might have other protections
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'No .htaccess file found in uploads directory. Direct PHP file execution may not be blocked.', 'wpshadow' ),
					'severity'      => 'critical',
					'threat_level'  => 80,
					'auto_fixable'  => true,
					'kb_link'       => 'https://wpshadow.com/kb/media-direct-file-access-security',
				);
			}

			// Check .htaccess content for PHP execution prevention
			$htaccess_content = file_get_contents( $htaccess_path );
			if ( false === $htaccess_content ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Could not read .htaccess file in uploads directory.', 'wpshadow' ),
					'severity'      => 'high',
					'threat_level'  => 70,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/media-direct-file-access-security',
				);
			}

			// Look for directives that prevent PHP execution
			$has_php_handler_block = false;
			
			// Common .htaccess directives that block PHP
			$php_block_patterns = array(
				'<FilesMatch \.php',
				'AddType text/plain',
				'php_flag engine off',
				'RemoveHandler .php',
				'RemoveType .php',
			);

			foreach ( $php_block_patterns as $pattern ) {
				if ( stripos( $htaccess_content, $pattern ) !== false ) {
					$has_php_handler_block = true;
					break;
				}
			}

			if ( ! $has_php_handler_block ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( '.htaccess file found but does not contain rules to block PHP execution. Direct PHP files can be executed.', 'wpshadow' ),
					'severity'      => 'critical',
					'threat_level'  => 80,
					'auto_fixable'  => true,
					'kb_link'       => 'https://wpshadow.com/kb/media-direct-file-access-security',
				);
			}
		}

		// All checks passed
		return null;
	}
}
