<?php
/**
 * Diagnostic: PHP is_uploaded_file Function
 *
 * Checks if is_uploaded_file() function is available for secure file upload validation.
 * This function prevents file upload spoofing attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Is_Uploaded_File
 *
 * Tests PHP is_uploaded_file() availability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Is_Uploaded_File extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-is-uploaded-file';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP is_uploaded_file Function';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if is_uploaded_file() function is available';

	/**
	 * Check PHP is_uploaded_file() function.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if is_uploaded_file function exists.
		if ( ! function_exists( 'is_uploaded_file' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP is_uploaded_file() function is not available. This function is critical for secure file upload validation and prevents file upload spoofing attacks. Check if it is disabled in disable_functions.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_is_uploaded_file',
				'meta'        => array(
					'function_exists' => false,
				),
			);
		}

		// Check if move_uploaded_file is also available (companion function).
		if ( ! function_exists( 'move_uploaded_file' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP move_uploaded_file() function is not available. While is_uploaded_file() exists, move_uploaded_file() is also required for secure file uploads. Check disable_functions.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_is_uploaded_file',
				'meta'        => array(
					'is_uploaded_file_exists'   => true,
					'move_uploaded_file_exists' => false,
				),
			);
		}

		// Check if upload_max_filesize is set (related configuration).
		$upload_max = ini_get( 'upload_max_filesize' );

		if ( empty( $upload_max ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP upload_max_filesize is not set. While file upload functions are available, uploads may not work without this configuration.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_is_uploaded_file',
				'meta'        => array(
					'is_uploaded_file_exists'   => true,
					'move_uploaded_file_exists' => true,
					'upload_max_filesize'       => '',
				),
			);
		}

		// Check if file_uploads is enabled.
		$file_uploads = ini_get( 'file_uploads' );

		if ( '0' === $file_uploads || 'Off' === $file_uploads || false === $file_uploads ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP file_uploads is disabled. While upload functions are available, file uploads will not work until this is enabled in php.ini.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_is_uploaded_file',
				'meta'        => array(
					'is_uploaded_file_exists'   => true,
					'move_uploaded_file_exists' => true,
					'file_uploads'              => false,
				),
			);
		}

		// PHP file upload functions are properly configured.
		return null;
	}
}
