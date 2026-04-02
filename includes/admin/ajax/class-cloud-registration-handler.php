<?php
/**
 * Cloud Registration AJAX Handlers
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\AJAX;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Integration\Cloud\Cloud_Service_Connector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WPSHADOW_PATH . 'includes/systems/core/class-ajax-handler-base.php';
require_once WPSHADOW_PATH . 'includes/systems/integration/class-cloud-service-connector.php';

/**
 * Cloud Registration Handler
 *
 * @since 1.6093.1200
 */
class Cloud_Registration_Handler extends AJAX_Handler_Base {

	/**
	 * Handle cloud registration.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_cloud_register', 'manage_options' );

		$email    = self::get_post_param( 'email', 'email', '', true );
		$site_url = get_site_url();

		$result = Cloud_Service_Connector::register( $email, $site_url );

		if ( $result['success'] ) {
			self::send_success( $result );
		} else {
			self::send_error( $result['message'] );
		}
	}
}

/**
 * Cloud Deregistration Handler
 *
 * @since 1.6093.1200
 */
class Cloud_Deregistration_Handler extends AJAX_Handler_Base {

	/**
	 * Handle cloud deregistration.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_cloud_deregister', 'manage_options' );

		$result = Cloud_Service_Connector::deregister();

		if ( $result['success'] ) {
			self::send_success( $result );
		} else {
			self::send_error( $result['message'] );
		}
	}
}

// Register AJAX handlers
add_action( 'wp_ajax_wpshadow_cloud_register', array( 'WPShadow\Admin\AJAX\Cloud_Registration_Handler', 'handle' ) );
add_action( 'wp_ajax_wpshadow_cloud_deregister', array( 'WPShadow\Admin\AJAX\Cloud_Deregistration_Handler', 'handle' ) );
