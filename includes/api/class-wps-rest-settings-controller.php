<?php
/**
 * Suite Configuration REST API Controller
 *
 * @package wpshadow_SUPPORT
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use WPS\CoreSupport\WPSHADOW_Settings;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Suite Configuration REST Controller
 */
class WPSHADOW_REST_Settings_Controller extends WPSHADOW_REST_Controller_Base {

	/**
	 * Register routes
	 */
	public function register_routes(): void {
		// GET /settings - Get all suite settings.
		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'module' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// PATCH /settings - Update suite settings.
		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'module'   => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
					'settings' => array(
						'required' => true,
						'type'     => 'object',
					),
					'network'  => array(
						'type'              => 'boolean',
						'default'           => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
				),
			)
		);

		// POST /settings/reset - Reset to defaults.
		register_rest_route(
			$this->namespace,
			'/settings/reset',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'reset_settings' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'module'  => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
					'network' => array(
						'type'              => 'boolean',
						'default'           => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
				),
			)
		);

		// GET /health - Health check endpoint.
		register_rest_route(
			$this->namespace,
			'/health',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'health_check' ),
				'permission_callback' => '__return_true', // Public endpoint.
			)
		);
	}

	/**
	 * Check permissions
	 *
	 * @return bool|WP_Error
	 */
	public function permissions_check() {
		return $this->check_permission( 'manage_options' );
	}

	/**
	 * Get settings
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_settings( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Settings' ) ) {
			return $this->error_response(
				'settings_not_available',
				__( 'Settings system is not available.', 'plugin-wpshadow' ),
				503
			);
		}

		$module = $request->get_param( 'module' );

		if ( $module ) {
			// Get settings for specific module.
			$settings = WPSHADOW_Settings::get_module_settings( $module );

			return $this->success_response(
				array(
					'module'   => $module,
					'settings' => $settings,
				)
			);
		}

		// Get all settings.
		$all_settings = WPSHADOW_Settings::get_all_settings();

		return $this->success_response( $all_settings );
	}

	/**
	 * Update settings
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_settings( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Settings' ) ) {
			return $this->error_response(
				'settings_not_available',
				__( 'Settings system is not available.', 'plugin-wpshadow' ),
				503
			);
		}

		$module   = $request->get_param( 'module' );
		$settings = $request->get_param( 'settings' );
		$network  = $request->get_param( 'network' );

		if ( empty( $module ) ) {
			return $this->error_response(
				'invalid_module',
				__( 'Module parameter is required.', 'plugin-wpshadow' ),
				400
			);
		}

		if ( ! is_array( $settings ) ) {
			return $this->error_response(
				'invalid_settings',
				__( 'Settings must be an object.', 'plugin-wpshadow' ),
				400
			);
		}

		// Update each setting.
		$updated_count = 0;
		foreach ( $settings as $key => $value ) {
			$result = WPSHADOW_Settings::update( $module, sanitize_key( $key ), $value, $network );
			if ( $result ) {
				++$updated_count;
			}
		}

		if ( 0 === $updated_count ) {
			return $this->error_response(
				'update_failed',
				__( 'Failed to update settings.', 'plugin-wpshadow' ),
				500
			);
		}

		return $this->success_response(
			array(
				'module'        => $module,
				'updated_count' => $updated_count,
			),
			sprintf(
				/* translators: %d: number of settings updated */
				__( '%d settings updated successfully.', 'plugin-wpshadow' ),
				$updated_count
			)
		);
	}

	/**
	 * Reset settings
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function reset_settings( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Settings' ) ) {
			return $this->error_response(
				'settings_not_available',
				__( 'Settings system is not available.', 'plugin-wpshadow' ),
				503
			);
		}

		// Check rate limit.
		$rate_check = $this->check_rate_limit( 'reset_settings', 5, 600 );
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}

		$module  = $request->get_param( 'module' );
		$network = $request->get_param( 'network' );

		if ( $module ) {
			// Reset specific module.
			$result = WPSHADOW_Settings::reset_module( $module, $network );

			if ( ! $result ) {
				return $this->error_response(
					'reset_failed',
					__( 'Failed to reset module settings.', 'plugin-wpshadow' ),
					500
				);
			}

			return $this->success_response(
				array( 'module' => $module ),
				sprintf(
					/* translators: %s: module name */
					__( 'Settings for %s reset to defaults.', 'plugin-wpshadow' ),
					$module
				)
			);
		}

		// Reset all settings.
		$result = WPSHADOW_Settings::reset_all( $network );

		if ( ! $result ) {
			return $this->error_response(
				'reset_failed',
				__( 'Failed to reset all settings.', 'plugin-wpshadow' ),
				500
			);
		}

		return $this->success_response(
			array( 'reset' => 'all' ),
			__( 'All settings reset to defaults.', 'plugin-wpshadow' )
		);
	}

	/**
	 * Health check endpoint
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function health_check( WP_REST_Request $request ) {
		$health_data = array(
			'status'    => 'healthy',
			'timestamp' => current_time( 'mysql' ),
			'version'   => defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : 'unknown',
			'checks'    => array(
				'database'  => $this->check_database(),
				'modules'   => $this->check_modules(),
				'vault'     => $this->check_vault(),
				'licensing' => $this->check_licensing(),
			),
		);

		// Determine overall health.
		$has_critical = false;
		foreach ( $health_data['checks'] as $check ) {
			if ( 'critical' === ( $check['status'] ?? '' ) ) {
				$has_critical = true;
				break;
			}
		}

		if ( $has_critical ) {
			$health_data['status'] = 'unhealthy';
		}

		return $this->success_response( $health_data );
	}

	/**
	 * Check database connectivity
	 *
	 * @return array
	 */
	private function check_database(): array {
		global $wpdb;

		$result = $wpdb->query( 'SELECT 1' );

		return array(
			'status'  => false !== $result ? 'ok' : 'critical',
			'message' => false !== $result ? 'Database is accessible' : 'Database connection failed',
		);
	}

	/**
	 * Check modules system
	 *
	 * @return array
	 */
	private function check_modules(): array {
		$available = class_exists( '\\WPShadow\\WPSHADOW_Module_Registry' );

		return array(
			'status'  => $available ? 'ok' : 'warning',
			'message' => $available ? 'Module system is available' : 'Module system not loaded',
		);
	}

	/**
	 * Check vault system
	 *
	 * @return array
	 */
	private function check_vault(): array {
		$available = class_exists( '\\WPShadow\\WPSHADOW_Vault' );

		return array(
			'status'  => $available ? 'ok' : 'info',
			'message' => $available ? 'Vault system is available' : 'Vault module not installed',
		);
	}

	/**
	 * Check licensing system
	 *
	 * @return array
	 */
	private function check_licensing(): array {
		$available = class_exists( '\\WPShadow\\WPSHADOW_License' );

		return array(
			'status'  => $available ? 'ok' : 'warning',
			'message' => $available ? 'Licensing system is available' : 'Licensing system not loaded',
		);
	}
}
