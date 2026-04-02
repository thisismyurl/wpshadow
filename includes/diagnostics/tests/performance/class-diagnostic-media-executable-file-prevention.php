<?php
/**
 * Media Executable File Prevention Diagnostic
 *
 * Validates prevention of executable file uploads by
 * checking allowed MIME types and upload filters.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Executable_File_Prevention Class
 *
 * Ensures executable file types are not allowed for upload.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Executable_File_Prevention extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-executable-file-prevention';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Executable File Prevention';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates prevention of executable file uploads';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$allowed_mimes = get_allowed_mime_types();

		$dangerous_extensions = array(
			'php',
			'php3',
			'php4',
			'php5',
			'phtml',
			'phar',
			'exe',
			'bat',
			'cmd',
			'com',
			'sh',
			'cgi',
			'pl',
			'py',
		);

		$dangerous_found = array();
		foreach ( $allowed_mimes as $exts => $mime ) {
			foreach ( $dangerous_extensions as $ext ) {
				if ( false !== strpos( $exts, $ext ) ) {
					$dangerous_found[] = $ext;
				}
			}
		}

		if ( ! empty( $dangerous_found ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated extensions */
				__( 'Executable file types are allowed for upload (%s); restrict dangerous extensions', 'wpshadow' ),
				implode( ', ', array_unique( $dangerous_found ) )
			);
		}

		if ( ! has_filter( 'wp_handle_upload_prefilter' ) ) {
			$issues[] = __( 'No upload prefilter detected; consider adding validation to block executable files', 'wpshadow' );
		}

		if ( ! function_exists( 'wp_check_filetype_and_ext' ) ) {
			$issues[] = __( 'wp_check_filetype_and_ext function is unavailable; upload validation may be limited', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-executable-file-prevention',
			);
		}

		return null;
	}
}
