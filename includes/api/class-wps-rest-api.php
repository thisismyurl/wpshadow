<?php
/**
 * REST API Initialization
 *
 * @package wpshadow_SUPPORT
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport\API;

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

		// Load and register modules controller.
		require_once __DIR__ . '/class-wps-rest-modules-controller.php';
		$modules_controller = new WPSHADOW_REST_Modules_Controller();
		$modules_controller->register_routes();

		// Load and register vault controller.
		require_once __DIR__ . '/class-wps-rest-vault-controller.php';
		$vault_controller = new WPSHADOW_REST_Vault_Controller();
		$vault_controller->register_routes();

		// Load and register license controller.
		require_once __DIR__ . '/class-wps-rest-license-controller.php';
		$license_controller = new WPSHADOW_REST_License_Controller();
		$license_controller->register_routes();

		// Load and register settings controller.
		require_once __DIR__ . '/class-wps-rest-settings-controller.php';
		$settings_controller = new WPSHADOW_REST_Settings_Controller();
		$settings_controller->register_routes();
	}
}
