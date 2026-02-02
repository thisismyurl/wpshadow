<?php
/**
 * Upload File Size Limit Diagnostic
 *
 * Checks if upload file size limits are properly configured.
 *
 * @since   1.26033.0901
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Upload_File_Size_Limit Class
 *
 * Validates upload file size limits.
 *
 * @since 1.26033.0901
 */
class Diagnostic_Upload_File_Size_Limit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-file-size-limit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload File Size Limit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if upload file size limits are properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'uploads';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0901
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get upload limits
		$max_upload_size = wp_max_upload_size();
		$post_max = (int) ini_get( 'post_max_size' ) * MB_IN_BYTES;
		$upload_max = (int) ini_get( 'upload_max_filesize' ) * MB_IN_BYTES;

		// Check if limits are too restrictive
		if ( $max_upload_size < 10 * MB_IN_BYTES ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: human-readable file size */
					__( 'Your upload limit is only %s. This may be too small for modern media. Consider increasing post_max_size or upload_max_filesize.', 'wpshadow' ),
					size_format( $max_upload_size )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upload-file-size-limit',
			);
		}

		// Check if limits are mismatched
		if ( abs( $post_max - $upload_max ) > 5 * MB_IN_BYTES ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your post_max_size and upload_max_filesize settings are significantly different. This can cause confusing upload errors.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upload-file-size-limit',
			);
		}

		return null; // Upload file size limits are properly configured
	}
}
