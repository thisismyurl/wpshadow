<?php
/**
 * REST API Initialization
 *
 * @package WPShadow
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPShadow\API;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Initialization Class
 */
class WPSHADOW_REST_API {

	/**
	 * Initialize REST API
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register all REST routes
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		// Load base controller.
		require_once __DIR__ . '/class-wps-rest-controller-base.php';

		// NOTE: Modules controller removed - modules are temporarily disabled
		// NOTE: Vault controller removed - moved to module-vault-wpshadow
		// NOTE: License controller removed - licensing is PRO plugin only

		// Load and register settings controller.
		require_once __DIR__ . '/class-wps-rest-settings-controller.php';
		$settings_controller = new WPSHADOW_REST_Settings_Controller();
		$settings_controller->register_routes();
	}
}
