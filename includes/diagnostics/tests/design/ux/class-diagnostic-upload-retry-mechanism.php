<?php
/**
 * Upload Retry Mechanism Diagnostic
 *
 * Detects when failed file uploads have no retry mechanism for users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\UX;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload Retry Mechanism Diagnostic Class
 *
 * Checks if file upload failures provide a retry option.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Upload_Retry_Mechanism extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-retry-mechanism';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Failed Upload Has No Retry Mechanism';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when file upload failures don\'t offer users a retry option';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		// Check if uploads are enabled.
		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) {
			return null; // Uploads disabled, not relevant.
		}

		// Check for upload-heavy plugins.
		$upload_plugins = array(
			'woocommerce/woocommerce.php'                       => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
			'wp-user-frontend/wpuf.php'                         => 'WP User Frontend',
			'buddypress/bp-loader.php'                          => 'BuddyPress',
			'bbpress/bbpress.php'                               => 'bbPress',
			'wp-job-manager/wp-job-manager.php'                 => 'WP Job Manager',
			'gravityforms/gravityforms.php'                     => 'Gravity Forms',
		);

		$active_upload_features = array();
		foreach ( $upload_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_upload_features[] = $name;
			}
		}

		// Check if media library is being used.
		$media_count = wp_count_attachments();
		$total_media = array_sum( (array) $media_count );

		if ( $total_media > 10 ) {
			$active_upload_features[] = __( 'Media Library', 'wpshadow' );
		}

		if ( empty( $active_upload_features ) ) {
			return null; // No significant upload usage.
		}

		// Check for retry mechanism plugins/scripts.
		global $wp_scripts;
		$has_retry_mechanism = false;

		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( preg_match( '/retry|resumable|plupload|dropzone|uppy/i', $handle ) ) {
					$has_retry_mechanism = true;
					break;
				}
			}
		}

		// Check for known plugins with retry functionality.
		$retry_plugins = array(
			'resumable-upload/resumable-upload.php',
			'wp-resumes-uploads/wp-resumes-uploads.php',
		);

		foreach ( $retry_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_retry_mechanism = true;
				break;
			}
		}

		if ( $has_retry_mechanism ) {
			return null; // Retry mechanism exists.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'When file uploads fail due to connection issues or timeouts, users have no way to retry. They must start completely over, losing their progress', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/upload-retry-mechanism',
			'context'      => array(
				'upload_features'   => $active_upload_features,
				'media_count'       => $total_media,
				'has_retry'         => $has_retry_mechanism,
				'impact'            => __( 'Users uploading large files or on slow connections lose all progress if upload fails. This is especially frustrating for 50MB+ files that take minutes to upload.', 'wpshadow' ),
				'recommendation'    => array(
					__( 'Implement automatic retry for failed uploads', 'wpshadow' ),
					__( 'Add "Retry Upload" button when upload fails', 'wpshadow' ),
					__( 'Consider chunked/resumable uploads for large files', 'wpshadow' ),
					__( 'Save partially uploaded files for retry', 'wpshadow' ),
					__( 'Show clear error messages with retry option', 'wpshadow' ),
					__( 'Consider plugins like Uppy or Dropzone.js', 'wpshadow' ),
				),
				'user_frustration'  => __( 'Failed uploads are the #2 reason users abandon forms (after length)', 'wpshadow' ),
			),
		);
	}
}
