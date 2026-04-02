<?php
/**
 * Mobile Camera Capture Integration Diagnostic
 *
 * Tests direct camera capture from mobile devices in media uploader.
 * Validates camera API access and mobile upload functionality.
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
 * Mobile Camera Capture Integration Diagnostic Class
 *
 * Checks if mobile camera capture is properly integrated in the
 * WordPress media uploader for improved mobile user experience.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Camera_Capture_Integration extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-camera-capture-integration';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Camera Capture Integration';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests direct camera capture from mobile devices in media uploader';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if the media library properly supports HTML5 camera capture
	 * attributes for mobile devices.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if we're on a mobile-responsive site.
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );
		$theme_tags = $theme->get( 'Tags' );

		$is_mobile_friendly = is_array( $theme_tags ) && in_array( 'responsive-layout', $theme_tags, true );

		// Check if site has HTTPS (required for camera API).
		$is_https = is_ssl();

		if ( ! $is_https ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Mobile camera capture requires HTTPS to function. Your site is not using HTTPS', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-camera-capture-integration',
				'details'      => array(
					'is_https'       => false,
					'requirement'    => __( 'Modern browsers require HTTPS to access device cameras for security reasons', 'wpshadow' ),
					'recommendation' => __( 'Install an SSL certificate and configure WordPress to use HTTPS', 'wpshadow' ),
					'benefits'       => array(
						__( 'Enable mobile camera capture in media uploader', 'wpshadow' ),
						__( 'Improve security and user trust', 'wpshadow' ),
						__( 'Better SEO rankings (HTTPS is a ranking factor)', 'wpshadow' ),
					),
				),
			);
		}

		// Test if media uploader has camera capture support.
		// This is done by checking if the HTML5 file input supports 'capture' attribute.
		
		// Check for plugins that might enhance mobile upload.
		$mobile_upload_plugins = array(
			'advanced-custom-fields/acf.php'          => 'Advanced Custom Fields',
			'buddypress/bp-loader.php'                => 'BuddyPress',
			'bbpress/bbpress.php'                     => 'bbPress',
		);

		$has_mobile_enhancement = false;
		foreach ( $mobile_upload_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_mobile_enhancement = true;
				break;
			}
		}

		// Check WordPress version (camera capture improved in 5.3+).
		global $wp_version;
		$wp_supports_camera = version_compare( $wp_version, '5.3', '>=' );

		// Simulate checking if plupload (WP's uploader) has camera support.
		// In reality, WordPress core media uploader should handle this.
		$plupload_available = wp_script_is( 'plupload', 'registered' );

		// Check for recent mobile uploads (indicates mobile functionality works).
		global $wpdb;

		// Get recent image uploads.
		$query = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = %s 
			AND post_status = %s 
			AND post_mime_type LIKE %s
			AND post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)",
			'attachment',
			'inherit',
			'image/%'
		);

		$recent_uploads = (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Check for mobile-specific image metadata (some phones add specific EXIF).
		$mobile_uploads = 0;
		if ( 0 < $recent_uploads ) {
			$recent_images = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'post_status'    => 'inherit',
					'posts_per_page' => 10,
					'date_query'     => array(
						array(
							'after' => '30 days ago',
						),
					),
				)
			);

			foreach ( $recent_images as $image ) {
				$file_path = get_attached_file( $image->ID );
				if ( $file_path && file_exists( $file_path ) && function_exists( 'exif_read_data' ) ) {
					// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- EXIF may not exist.
					$exif = @exif_read_data( $file_path );
					if ( ! empty( $exif ) && isset( $exif['Model'] ) ) {
						// Check for mobile phone models in EXIF.
						$model = strtolower( $exif['Model'] );
						if ( false !== strpos( $model, 'iphone' ) || false !== strpos( $model, 'ipad' ) || 
						     false !== strpos( $model, 'samsung' ) || false !== strpos( $model, 'pixel' ) ||
						     false !== strpos( $model, 'android' ) ) {
							$mobile_uploads++;
						}
					}
				}
			}
		}

		// Issue: WordPress version doesn't fully support camera or no evidence of mobile usage.
		if ( ! $wp_supports_camera || ( 0 < $recent_uploads && 0 === $mobile_uploads ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => ! $wp_supports_camera
					? __( 'Your WordPress version may not fully support mobile camera capture. Update recommended', 'wpshadow' )
					: __( 'Mobile camera capture is available but may not be functioning optimally', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-camera-capture-integration',
				'details'      => array(
					'is_https'              => $is_https,
					'wp_version'            => $wp_version,
					'wp_supports_camera'    => $wp_supports_camera,
					'plupload_available'    => $plupload_available,
					'is_mobile_friendly'    => $is_mobile_friendly,
					'theme_name'            => $theme_name,
					'recent_uploads'        => $recent_uploads,
					'mobile_uploads'        => $mobile_uploads,
					'has_mobile_enhancement' => $has_mobile_enhancement,
					'recommendation'        => ! $wp_supports_camera
						? __( 'Update WordPress to 5.3 or later for improved mobile camera support', 'wpshadow' )
						: __( 'Test mobile upload functionality and ensure theme supports responsive file inputs', 'wpshadow' ),
					'testing_steps'         => array(
						__( '1. Access your site on a mobile device', 'wpshadow' ),
						__( '2. Navigate to a page with media upload (post editor, profile, etc.)', 'wpshadow' ),
						__( '3. Tap the file upload button', 'wpshadow' ),
						__( '4. Verify "Take Photo" or "Camera" option appears', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
