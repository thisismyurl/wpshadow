<?php
/**
 * Module Management REST API Controller
 *
 * @package wpshadow_SUPPORT
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPShadow\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use WPShadow\WPSHADOW_Module_Registry;
use WPShadow\WPSHADOW_Module_Actions;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Management REST Controller
 */
class WPSHADOW_REST_Modules_Controller extends WPSHADOW_REST_Controller_Base {

	/**
	 * Register routes
	 */
	public function register_routes(): void {
		// GET /modules - List all modules.
		register_rest_route(
			$this->namespace,
			'/modules',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_modules' ),
				'permission_callback' => array( $this, 'get_modules_permissions_check' ),
				'args'                => $this->get_collection_params(),
			)
		);

		// GET /modules/{slug}/status - Get module status.
		register_rest_route(
			$this->namespace,
			'/modules/(?P<slug>[a-zA-Z0-9_-]+)/status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_module_status' ),
				'permission_callback' => array( $this, 'get_modules_permissions_check' ),
				'args'                => array(
					'slug' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// POST /modules/{slug}/install - Install module.
		register_rest_route(
			$this->namespace,
			'/modules/(?P<slug>[a-zA-Z0-9_-]+)/install',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'install_module' ),
				'permission_callback' => array( $this, 'manage_modules_permissions_check' ),
				'args'                => array(
					'slug' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// POST /modules/{slug}/activate - Activate module.
		register_rest_route(
			$this->namespace,
			'/modules/(?P<slug>[a-zA-Z0-9_-]+)/activate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'activate_module' ),
				'permission_callback' => array( $this, 'manage_modules_permissions_check' ),
				'args'                => array(
					'slug'    => array(
						'required'          => true,
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

		// POST /modules/{slug}/deactivate - Deactivate module.
		register_rest_route(
			$this->namespace,
			'/modules/(?P<slug>[a-zA-Z0-9_-]+)/deactivate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'deactivate_module' ),
				'permission_callback' => array( $this, 'manage_modules_permissions_check' ),
				'args'                => array(
					'slug'    => array(
						'required'          => true,
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

		// DELETE /modules/{slug} - Uninstall module.
		register_rest_route(
			$this->namespace,
			'/modules/(?P<slug>[a-zA-Z0-9_-]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'uninstall_module' ),
				'permission_callback' => array( $this, 'manage_modules_permissions_check' ),
				'args'                => array(
					'slug' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// PATCH /modules/{slug}/toggle/{feature} - Toggle feature flag.
		register_rest_route(
			$this->namespace,
			'/modules/(?P<slug>[a-zA-Z0-9_-]+)/toggle/(?P<feature>[a-zA-Z0-9_-]+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'toggle_feature' ),
				'permission_callback' => array( $this, 'manage_modules_permissions_check' ),
				'args'                => array(
					'slug'    => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
					'feature' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
					'enabled' => array(
						'required'          => true,
						'type'              => 'boolean',
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
				),
			)
		);
	}

	/**
	 * Check permissions for reading modules
	 *
	 * @return bool|WP_Error
	 */
	public function get_modules_permissions_check() {
		return $this->check_permission( 'manage_options' );
	}

	/**
	 * Check permissions for managing modules
	 *
	 * @return bool|WP_Error
	 */
	public function manage_modules_permissions_check() {
		return $this->check_permission( 'manage_options' );
	}

	/**
	 * Get all modules
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_modules( WP_REST_Request $request ) {
		$params  = $this->get_pagination_params( $request );
		$catalog = WPSHADOW_Module_Registry::get_catalog_with_status();

		// Apply filters.
		$type = $request->get_param( 'type' );
		if ( $type && in_array( $type, array( 'hub', 'spoke' ), true ) ) {
			$catalog = array_filter(
				$catalog,
				function ( $module ) use ( $type ) {
					return ( $module['type'] ?? '' ) === $type;
				}
			);
		}

		$status = $request->get_param( 'status' );
		if ( $status ) {
			$catalog = array_filter(
				$catalog,
				function ( $module ) use ( $status ) {
					if ( 'active' === $status ) {
						return ! empty( $module['status']['active'] );
					}
					if ( 'installed' === $status ) {
						return ! empty( $module['installed'] );
					}
					if ( 'available' === $status ) {
						return empty( $module['installed'] );
					}
					return true;
				}
			);
		}

		$total   = count( $catalog );
		$catalog = array_slice( $catalog, $params['offset'], $params['per_page'] );

		$response = $this->success_response( array_values( $catalog ) );
		return $this->add_pagination_headers( $response, $total, $params['page'], $params['per_page'] );
	}

	/**
	 * Get module status
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_module_status( WP_REST_Request $request ) {
		$slug = $this->validate_slug( $request->get_param( 'slug' ) );
		if ( is_wp_error( $slug ) ) {
			return $slug;
		}

		$catalog = WPSHADOW_Module_Registry::get_catalog_with_status();
		$module  = null;

		foreach ( $catalog as $item ) {
			if ( $item['slug'] === $slug ) {
				$module = $item;
				break;
			}
		}

		if ( ! $module ) {
			return $this->error_response(
				'module_not_found',
				__( 'Module not found.', 'plugin-wpshadow' ),
				404
			);
		}

		return $this->success_response( $module );
	}

	/**
	 * Install module
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function install_module( WP_REST_Request $request ) {
		// Check rate limit.
		$rate_check = $this->check_rate_limit( 'install_module', 5, 300 );
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}

		$slug = $this->validate_slug( $request->get_param( 'slug' ) );
		if ( is_wp_error( $slug ) ) {
			return $slug;
		}

		// Find module in catalog.
		$catalog = WPSHADOW_Module_Registry::get_catalog_with_status();
		$module  = null;

		foreach ( $catalog as $item ) {
			if ( $item['slug'] === $slug ) {
				$module = $item;
				break;
			}
		}

		if ( ! $module ) {
			return $this->error_response(
				'module_not_found',
				__( 'Module not found in catalog.', 'plugin-wpshadow' ),
				404
			);
		}

		if ( ! empty( $module['installed'] ) ) {
			return $this->error_response(
				'already_installed',
				__( 'Module is already installed.', 'plugin-wpshadow' ),
				400
			);
		}

		// Use existing install logic.
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Module_Actions' ) ) {
			return $this->error_response(
				'install_failed',
				__( 'Module installer not available.', 'plugin-wpshadow' ),
				500
			);
		}

		$result = WPSHADOW_Module_Actions::install_module( $module );

		if ( is_wp_error( $result ) ) {
			return $this->error_response(
				'install_failed',
				$result->get_error_message(),
				500
			);
		}

		// Refresh cache.
		WPSHADOW_Module_Registry::clear_cache();

		return $this->success_response(
			array( 'slug' => $slug ),
			__( 'Module installed successfully.', 'plugin-wpshadow' ),
			201
		);
	}

	/**
	 * Activate module
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function activate_module( WP_REST_Request $request ) {
		$slug = $this->validate_slug( $request->get_param( 'slug' ) );
		if ( is_wp_error( $slug ) ) {
			return $slug;
		}

		$network = $request->get_param( 'network' );
		$plugin  = $slug . '/' . $slug . '.php';

		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$result = activate_plugin( $plugin, '', $network );

		if ( is_wp_error( $result ) ) {
			return $this->error_response(
				'activation_failed',
				$result->get_error_message(),
				500
			);
		}

		// Refresh cache.
		WPSHADOW_Module_Registry::clear_cache();

		return $this->success_response(
			array( 'slug' => $slug ),
			__( 'Module activated successfully.', 'plugin-wpshadow' )
		);
	}

	/**
	 * Deactivate module
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function deactivate_module( WP_REST_Request $request ) {
		$slug = $this->validate_slug( $request->get_param( 'slug' ) );
		if ( is_wp_error( $slug ) ) {
			return $slug;
		}

		$network = $request->get_param( 'network' );
		$plugin  = $slug . '/' . $slug . '.php';

		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		deactivate_plugins( $plugin, false, $network );

		// Refresh cache.
		WPSHADOW_Module_Registry::clear_cache();

		return $this->success_response(
			array( 'slug' => $slug ),
			__( 'Module deactivated successfully.', 'plugin-wpshadow' )
		);
	}

	/**
	 * Uninstall module
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function uninstall_module( WP_REST_Request $request ) {
		// Check rate limit.
		$rate_check = $this->check_rate_limit( 'uninstall_module', 3, 300 );
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}

		$slug = $this->validate_slug( $request->get_param( 'slug' ) );
		if ( is_wp_error( $slug ) ) {
			return $slug;
		}

		$plugin = $slug . '/' . $slug . '.php';

		if ( ! function_exists( 'delete_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Deactivate first.
		deactivate_plugins( $plugin );

		// Delete plugin.
		$result = delete_plugins( array( $plugin ) );

		if ( is_wp_error( $result ) ) {
			return $this->error_response(
				'uninstall_failed',
				$result->get_error_message(),
				500
			);
		}

		// Refresh cache.
		WPSHADOW_Module_Registry::clear_cache();

		return $this->success_response(
			array( 'slug' => $slug ),
			__( 'Module uninstalled successfully.', 'plugin-wpshadow' )
		);
	}

	/**
	 * Toggle feature flag
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function toggle_feature( WP_REST_Request $request ) {
		$slug = $this->validate_slug( $request->get_param( 'slug' ) );
		if ( is_wp_error( $slug ) ) {
			return $slug;
		}

		$feature = $this->validate_slug( $request->get_param( 'feature' ) );
		if ( is_wp_error( $feature ) ) {
			return $feature;
		}

		$enabled = $request->get_param( 'enabled' );

		// Use settings API to store feature flags.
		$settings_key = "feature_{$feature}_enabled";
		$result       = \WPShadow\WPSHADOW_Settings::update( $slug, $settings_key, $enabled );

		if ( ! $result ) {
			return $this->error_response(
				'toggle_failed',
				__( 'Couldn\'t toggle that feature.', 'plugin-wpshadow' ),
				500
			);
		}

		return $this->success_response(
			array(
				'slug'    => $slug,
				'feature' => $feature,
				'enabled' => $enabled,
			),
			sprintf(
				/* translators: %s: feature name */
				__( 'Feature %s toggled successfully.', 'plugin-wpshadow' ),
				$feature
			)
		);
	}

	/**
	 * Get collection parameters
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		return array(
			'page'     => array(
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'type'              => 'integer',
				'default'           => 20,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
			),
			'type'     => array(
				'type'              => 'string',
				'enum'              => array( 'hub', 'spoke' ),
				'sanitize_callback' => 'sanitize_key',
			),
			'status'   => array(
				'type'              => 'string',
				'enum'              => array( 'active', 'installed', 'available' ),
				'sanitize_callback' => 'sanitize_key',
			),
		);
	}
}
