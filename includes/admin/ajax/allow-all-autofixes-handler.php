<?php
/**
 * Allow All Autofixes AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Allow_All_Autofixes_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX hooks for global auto-fix permission changes.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_allow_all_autofixes', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle global auto-fix permission change requests.
	 *
	 * @since 0.6093.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_allow_all_autofixes', 'manage_options', 'nonce' );

		$enabled = self::get_post_param( 'enabled', 'bool', false );
		update_option( 'wpshadow_allow_all_autofixes', (bool) $enabled );

		self::send_success(
			array(
				'message' => $enabled ? __( 'All auto-fixes enabled.', 'wpshadow' ) : __( 'All auto-fixes disabled.', 'wpshadow' ),
				'enabled' => (bool) $enabled,
			)
		);
	}
}
