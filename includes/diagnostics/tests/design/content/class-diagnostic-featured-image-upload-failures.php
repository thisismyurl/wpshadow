<?php
/**
 * Featured Image Upload Failures Diagnostic
 *
 * Monitors featured image uploads for failures. Tests upload process
 * and thumbnail generation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Featured Image Upload Failures Diagnostic Class
 *
 * Checks for issues preventing featured images from uploading
 * or thumbnails from generating properly.
 *
 * @since 1.6033.1340
 */
class Diagnostic_Featured_Image_Upload_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'featured-image-upload-failures';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Featured Image Upload Failures';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors featured image uploads for failures and thumbnail generation issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if theme supports post-thumbnails.
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			$issues[] = __( 'Theme does not support featured images', 'wpshadow' );
		}

		// Check for images uploaded recently but missing thumbnail metadata.
		$images_missing_thumbs = $wpdb->get_var(
			"SELECT COUNT(p.ID)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_metadata'
			WHERE p.post_type = 'attachment'
			AND p.post_mime_type LIKE 'image/%'
			AND p.post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
			AND (pm.meta_value IS NULL OR pm.meta_value = '')"
		);

		if ( $images_missing_thumbs > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				__( '%d recent images missing thumbnail metadata', 'wpshadow' ),
				$images_missing_thumbs
			);
		}

		// Check upload directory permissions.
		$upload_dir = wp_upload_dir();
		if ( ! wp_is_writable( $upload_dir['path'] ) ) {
			$issues[] = __( 'Upload directory not writable', 'wpshadow' );
		}

		// Check for GD or Imagick availability (required for thumbnails).
		$has_image_library = false;
		if ( extension_loaded( 'gd' ) ) {
			$has_image_library = true;
		} elseif ( extension_loaded( 'imagick' ) ) {
			$has_image_library = true;
		}

		if ( ! $has_image_library ) {
			$issues[] = __( 'No image processing library (GD or Imagick) available', 'wpshadow' );
		}

		// Check memory limit for image processing.
		$memory_limit = ini_get( 'memory_limit' );
		if ( $memory_limit ) {
			$memory_limit_bytes = wp_convert_hr_to_bytes( $memory_limit );
			if ( $memory_limit_bytes < 67108864 ) { // 64MB.
				$issues[] = sprintf(
					/* translators: %s: memory limit */
					__( 'PHP memory limit (%s) may be too low for large images', 'wpshadow' ),
					$memory_limit
				);
			}
		}

		// Check for posts with featured image meta but missing actual file.
		$missing_featured_images = $wpdb->get_var(
			"SELECT COUNT(pm.post_id)
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.meta_value = p.ID
			WHERE pm.meta_key = '_thumbnail_id'
			AND (p.ID IS NULL OR p.post_type != 'attachment')"
		);

		if ( $missing_featured_images > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts reference missing featured images', 'wpshadow' ),
				$missing_featured_images
			);
		}

		// Check PHP file upload settings.
		$upload_max_filesize = ini_get( 'upload_max_filesize' );
		$post_max_size       = ini_get( 'post_max_size' );

		if ( $upload_max_filesize ) {
			$upload_bytes = wp_convert_hr_to_bytes( $upload_max_filesize );
			if ( $upload_bytes < 2097152 ) { // Less than 2MB.
				$issues[] = sprintf(
					/* translators: %s: file size limit */
					__( 'Upload limit (%s) may be too small for high-quality images', 'wpshadow' ),
					$upload_max_filesize
				);
			}
		}

		// Check for failed image uploads in recent error logs.
		$recent_failed_uploads = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_status = 'trash'
			AND post_mime_type LIKE 'image/%'
			AND post_date > DATE_SUB(NOW(), INTERVAL 7 DAY)"
		);

		if ( $recent_failed_uploads > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failed uploads */
				__( '%d image uploads trashed in last 7 days', 'wpshadow' ),
				$recent_failed_uploads
			);
		}

		// Check for intermediate image sizes configuration.
		$image_sizes = wp_get_registered_image_subsizes();
		if ( empty( $image_sizes ) ) {
			$issues[] = __( 'No image sizes registered (thumbnail generation may fail)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/featured-image-upload-failures',
			);
		}

		return null;
	}
}
