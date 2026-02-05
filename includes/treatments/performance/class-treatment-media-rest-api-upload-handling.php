<?php
/**
 * Media REST API Upload Handling Treatment
 *
 * Checks if REST API media uploads are properly validated and sanitized.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media REST API Upload Handling Treatment Class
 *
 * Verifies that file uploads via REST API are properly validated,
 * sanitized, and stored securely with appropriate metadata.
 *
 * @since 1.6033.0000
 */
class Treatment_Media_Rest_Api_Upload_Handling extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-rest-api-upload-handling';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media REST API Upload Handling';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API media uploads are properly validated and sanitized';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if REST API media controller is loaded.
		if ( ! class_exists( 'WP_REST_Attachments_Controller' ) ) {
			$issues[] = __( 'REST API Attachments Controller is not available', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-rest-api-upload-handling',
			);
		}

		// Check for file upload validation filters.
		$has_upload_validation = has_filter( 'wp_handle_upload_prefilter' );
		if ( ! $has_upload_validation ) {
			$issues[] = __( 'No upload validation filters detected', 'wpshadow' );
		}

		// Check for MIME type validation.
		$has_mime_validation = has_filter( 'upload_mimes' );
		if ( ! $has_mime_validation ) {
			$issues[] = __( 'No MIME type validation filters detected', 'wpshadow' );
		}

		// Check if file extension validation exists.
		$has_extension_check = has_filter( 'wp_check_filetype_and_ext' );
		if ( ! $has_extension_check ) {
			$issues[] = __( 'No file extension validation filters detected', 'wpshadow' );
		}

		// Check upload directory permissions.
		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) {
			$issues[] = sprintf(
				/* translators: %s: error message */
				__( 'Upload directory error: %s', 'wpshadow' ),
				$upload_dir['error']
			);
		}

		// Check if uploads directory is writable.
		if ( ! wp_is_writable( $upload_dir['path'] ) ) {
			$issues[] = __( 'Uploads directory is not writable', 'wpshadow' );
		}

		// Check for file sanitization on REST upload.
		$has_rest_sanitization = has_filter( 'rest_pre_insert_attachment' );
		if ( ! $has_rest_sanitization ) {
			$issues[] = __( 'No REST API attachment sanitization filters detected', 'wpshadow' );
		}

		// Check for metadata generation after upload.
		$has_metadata_filter = has_filter( 'wp_generate_attachment_metadata' );
		if ( ! $has_metadata_filter ) {
			$issues[] = __( 'No attachment metadata generation filters detected', 'wpshadow' );
		}

		// Check allowed file types.
		$allowed_mimes = get_allowed_mime_types();
		if ( empty( $allowed_mimes ) ) {
			$issues[] = __( 'No file types are allowed for upload', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-rest-api-upload-handling',
			);
		}

		return null;
	}
}
