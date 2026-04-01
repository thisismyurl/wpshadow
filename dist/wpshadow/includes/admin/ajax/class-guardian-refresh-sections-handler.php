<?php
/**
 * AJAX Handler: Guardian Dashboard Refresh
 *
 * Refreshes Diagnostics Schedule and Activity Log sections on demand.
 *
 * @package WPShadow
 * @subpackage Admin/AJAX
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\AJAX;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guardian dashboard refresh handler.
 *
 * @since 0.6093.1200
 */
class Guardian_Refresh_Sections_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_guardian_refresh_sections', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle section refresh request.
	 *
	 * @since 0.6093.1200
	 * @return void Sends JSON response.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_guardian', 'manage_options' );

		if ( ! class_exists( '\\WPShadow\\Admin\\Guardian_Dashboard' ) ) {
			self::send_error( __( 'Guardian dashboard is unavailable.', 'wpshadow' ) );
		}

		self::send_success(
			array(
				'diagnostics_overview' => \WPShadow\Admin\Guardian_Dashboard::get_diagnostics_overview_html(),
				'activity_log'         => \WPShadow\Admin\Guardian_Dashboard::get_activity_timeline_html(),
			)
		);
	}
}

Guardian_Refresh_Sections_Handler::register();
