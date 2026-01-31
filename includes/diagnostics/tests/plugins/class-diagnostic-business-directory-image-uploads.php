<?php
/**
 * Business Directory Image Uploads Diagnostic
 *
 * Business Directory uploads not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.549.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Directory Image Uploads Diagnostic Class
 *
 * @since 1.549.0000
 */
class Diagnostic_BusinessDirectoryImageUploads extends Diagnostic_Base {

	protected static $slug = 'business-directory-image-uploads';
	protected static $title = 'Business Directory Image Uploads';
	protected static $description = 'Business Directory uploads not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify file type restrictions
		$allowed_types = get_option( 'wpbdp_image_allowed_types', array() );
		if ( empty( $allowed_types ) || in_array( 'php', $allowed_types, true ) || in_array( 'exe', $allowed_types, true ) ) {
			$issues[] = __( 'Dangerous file types not restricted in uploads', 'wpshadow' );
		}

		// Check 2: Check image upload size limits
		$max_size = get_option( 'wpbdp_image_max_size', 0 );
		if ( $max_size > ( 5 * 1024 * 1024 ) || $max_size === 0 ) {
			$issues[] = __( 'Image upload size limit too high or not configured', 'wpshadow' );
		}

		// Check 3: Verify upload validation is enabled
		$validate_uploads = get_option( 'wpbdp_validate_image_uploads', false );
		if ( ! $validate_uploads ) {
			$issues[] = __( 'Image upload validation not enabled', 'wpshadow' );
		}

		// Check 4: Check automatic image optimization
		$optimize_images = get_option( 'wpbdp_optimize_uploaded_images', false );
		if ( ! $optimize_images ) {
			$issues[] = __( 'Automatic image optimization not configured', 'wpshadow' );
		}

		// Check 5: Verify SSL for image uploads
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSL not enabled for secure image uploads', 'wpshadow' );
		}

		// Check 6: Check upload moderation requirement
		$moderate_uploads = get_option( 'wpbdp_moderate_image_uploads', false );
		if ( ! $moderate_uploads ) {
			$issues[] = __( 'Image upload moderation not required', 'wpshadow' );
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
