<?php
/**
 * Capture Screenshot AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Visual_Comparator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capture Screenshot Handler
 */
class Capture_Screenshot_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX action
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_capture_screenshot', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle screenshot capture request
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_visual_comparison', 'manage_options', 'nonce' );

		$url   = self::get_post_param( 'url', 'url', '' );
		$label = self::get_post_param( 'label', 'text', '' );

		if ( empty( $url ) ) {
			self::send_error( __( 'Please enter a valid URL.', 'wpshadow' ) );
		}

		// Validate URL is from this site
		$site_url = home_url();
		if ( strpos( $url, $site_url ) !== 0 ) {
			self::send_error( __( 'You can only capture screenshots of your own site.', 'wpshadow' ) );
		}

		// Capture screenshot using Visual_Comparator
		$screenshot_path = Visual_Comparator::capture_screenshot( $url, $label ? $label : 'manual' );

		if ( ! $screenshot_path ) {
			self::send_error( __( 'Failed to capture screenshot. Please try again.', 'wpshadow' ) );
		}

		// Get screenshot URL
		$upload_dir      = wp_upload_dir();
		$screenshot_url  = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $screenshot_path );

		self::send_success(
			array(
				'screenshot_url'  => $screenshot_url,
				'screenshot_path' => $screenshot_path,
				'page_url'        => $url,
				'label'           => $label,
				'timestamp'       => current_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
			)
		);
	}
}
