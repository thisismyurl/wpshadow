<?php
/**
 * Mobile Upload Compatibility Diagnostic
 *
 * Tests file uploads from mobile devices. Verifies camera/gallery access.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Mobile_Upload_Compatibility Class
 *
 * Validates mobile device upload compatibility. Mobile browsers have specific
 * requirements for file uploads, especially camera/gallery access via HTML5
 * input accept attributes and capture capabilities.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Mobile_Upload_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-upload-compatibility';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Upload Compatibility';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests file uploads from mobile devices';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Mobile-friendly upload interface
	 * - File input accept attributes
	 * - Plupload mobile runtime (HTML5)
	 * - Mobile-specific upload errors
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check Plupload configuration for mobile support.
		$plupload_settings = wp_plupload_default_settings();

		// Check if HTML5 runtime is available (required for mobile).
		if ( ! empty( $plupload_settings['runtimes'] ) ) {
			$runtimes = explode( ',', $plupload_settings['runtimes'] );
			$runtimes = array_map( 'trim', $runtimes );
			
			if ( ! in_array( 'html5', $runtimes, true ) ) {
				$issues[] = __( 'HTML5 runtime not enabled in Plupload - required for mobile uploads', 'wpshadow' );
			}

			// Flash/Silverlight should not be first (mobile doesn't support).
			if ( isset( $runtimes[0] ) && in_array( $runtimes[0], array( 'flash', 'silverlight' ), true ) ) {
				$issues[] = sprintf(
					/* translators: %s: runtime name */
					__( 'Non-mobile runtime (%s) is first priority - mobile uploads will fail', 'wpshadow' ),
					$runtimes[0]
				);
			}
		}

		// Check for viewport meta tag (required for proper mobile rendering).
		ob_start();
		wp_head();
		$head_content = ob_get_clean();

		if ( false === strpos( $head_content, 'viewport' ) ) {
			$issues[] = __( 'Viewport meta tag missing - mobile upload UI may not render properly', 'wpshadow' );
		}

		// Check for responsive CSS media queries in admin.
		global $wp_styles;
		$has_responsive_css = false;
		
		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $style ) {
				if ( ! empty( $style->extra ) && isset( $style->extra['media'] ) ) {
					if ( false !== strpos( $style->extra['media'], 'max-width' ) ) {
						$has_responsive_css = true;
						break;
					}
				}
			}
		}

		if ( ! $has_responsive_css ) {
			$issues[] = __( 'No responsive CSS detected - mobile upload interface may be unusable', 'wpshadow' );
		}

		// Check for mobile-specific upload errors in database.
		global $wpdb;

		// Check for common mobile upload patterns in failed uploads.
		$mobile_user_agents = array( 'iPhone', 'iPad', 'Android', 'Mobile', 'iPod' );
		$mobile_errors      = 0;

		// Check transients for mobile upload errors.
		foreach ( $mobile_user_agents as $ua ) {
			$errors = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->options}
					WHERE option_name LIKE %s
					AND option_value LIKE %s",
					$wpdb->esc_like( '_transient_upload_error_' ) . '%',
					'%' . $wpdb->esc_like( $ua ) . '%'
				)
			);
			$mobile_errors += (int) $errors;
		}

		if ( $mobile_errors > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of mobile errors */
				_n(
					'%d mobile upload error detected',
					'%d mobile upload errors detected',
					$mobile_errors,
					'wpshadow'
				),
				$mobile_errors
			);
		}

		// Check PHP settings that commonly cause mobile upload issues.
		$upload_max = wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
		$post_max   = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );

		// Mobile photos can be 5-15MB, check if limits accommodate.
		$fifteen_mb = 15 * 1024 * 1024;
		if ( $upload_max < $fifteen_mb ) {
			$issues[] = sprintf(
				/* translators: %s: current limit */
				__( 'upload_max_filesize (%s) is low - mobile photos often 5-15MB', 'wpshadow' ),
				size_format( $upload_max )
			);
		}

		// Check for max_input_time (mobile uploads on slow connections).
		$max_input_time = (int) ini_get( 'max_input_time' );
		if ( $max_input_time > 0 && $max_input_time < 120 ) {
			$issues[] = sprintf(
				/* translators: %d: seconds */
				__( 'max_input_time (%d seconds) is low - mobile uploads on slow networks may timeout', 'wpshadow' ),
				$max_input_time
			);
		}

		// Check for allowed image MIME types (mobile cameras).
		$allowed_mimes = get_allowed_mime_types();
		$required_mobile_types = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'heic'         => 'image/heic', // iPhone photos.
			'heif'         => 'image/heif', // iPhone photos.
		);

		foreach ( $required_mobile_types as $ext => $mime ) {
			$found = false;
			foreach ( $allowed_mimes as $allowed_ext => $allowed_mime ) {
				if ( $mime === $allowed_mime || false !== strpos( $allowed_ext, $ext ) ) {
					$found = true;
					break;
				}
			}
			
			if ( ! $found ) {
				$issues[] = sprintf(
					/* translators: 1: file extension, 2: MIME type */
					__( 'Mobile photo format not allowed: %1$s (%2$s)', 'wpshadow' ),
					$ext,
					$mime
				);
			}
		}

		// Check for HEIC/HEIF support (iPhone default since iOS 11).
		if ( ! isset( $allowed_mimes['heic'] ) && ! isset( $allowed_mimes['heif'] ) ) {
			$issues[] = __( 'HEIC/HEIF formats not allowed - iPhone users must convert photos manually', 'wpshadow' );
		}

		// Check for touch event support in Plupload settings.
		if ( isset( $plupload_settings['drop_element'] ) ) {
			// Drop zones should also support touch.
			$issues[] = __( 'Drag-and-drop configured but may not support mobile touch events', 'wpshadow' );
		}

		// Check for file size restrictions that might block mobile photos.
		$wp_max_upload = wp_max_upload_size();
		$ten_mb = 10 * 1024 * 1024;
		
		if ( $wp_max_upload < $ten_mb ) {
			$issues[] = sprintf(
				/* translators: %s: current limit */
				__( 'WordPress max upload size (%s) is too low for mobile photos', 'wpshadow' ),
				size_format( $wp_max_upload )
			);
		}

		// Check for SSL (required for camera access in modern mobile browsers).
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using HTTPS - modern mobile browsers require SSL for camera/media access', 'wpshadow' );
		}

		// Check for mobile-blocking security plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$mobile_blockers = array(
			'wp-cerber' => __( 'WP Cerber - may block mobile user agents', 'wpshadow' ),
		);

		foreach ( $mobile_blockers as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = $message;
					break;
				}
			}
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d mobile upload compatibility issue detected',
						'%d mobile upload compatibility issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-upload-compatibility',
				'details'      => array(
					'issues'         => $issues,
					'ssl_enabled'    => is_ssl(),
					'upload_max'     => size_format( $upload_max ),
					'post_max'       => size_format( $post_max ),
					'wp_max_upload'  => size_format( $wp_max_upload ),
					'mobile_errors'  => $mobile_errors,
					'allowed_mimes'  => array_keys( $allowed_mimes ),
				),
			);
		}

		return null;
	}
}
