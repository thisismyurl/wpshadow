<?php
/**
 * Save Tagline AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Save_Tagline_Handler extends AJAX_Handler_Base {
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_save_tagline', array( __CLASS__, 'handle' ) );
	}

	public static function handle(): void {
		self::verify_request( 'wpshadow_save_tagline', 'manage_options', 'nonce' );

		$tagline = self::get_post_param( 'tagline', 'text', '' );
		if ( empty( $tagline ) ) {
			self::send_error( __( 'Please enter a tagline.', 'wpshadow' ) );
		}
		if ( strlen( $tagline ) > 200 ) {
			self::send_error( __( 'Tagline is too long.', 'wpshadow' ) );
		}

		update_option( 'blogdescription', $tagline );

		// Log activity (#565: Activity Logging Expansion)
		Activity_Logger::log(
			'site_settings_changed',
			sprintf( __( 'Site tagline updated to: "%s"', 'wpshadow' ), $tagline ),
			'wordpress_config',
			array( 'tagline' => $tagline )
		);

		self::send_success( array( 'message' => __( 'Tagline saved successfully!', 'wpshadow' ) ) );
	}
}
