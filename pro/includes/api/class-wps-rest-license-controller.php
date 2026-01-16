<?php
/**
 * License Management REST API Controller
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
use WPShadow\WPSHADOW_License;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * License Management REST Controller
 */
class WPSHADOW_REST_License_Controller extends WPSHADOW_REST_Controller_Base {

	/**
	 * Register routes
	 */
	public function register_routes(): void {
		// GET /license - Get license status.
		register_rest_route(
			$this->namespace,
			'/license',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_license' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			)
		);

		// POST /license/register - Register license key.
		register_rest_route(
			$this->namespace,
			'/license/register',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'register_license' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'license_key' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// DELETE /license - Remove license.
		register_rest_route(
			$this->namespace,
			'/license',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'remove_license' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			)
		);

		// POST /license/verify - Force license verification.
		register_rest_route(
			$this->namespace,
			'/license/verify',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'verify_license' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			)
		);

		// GET /license/network - Network license status (multisite).
		register_rest_route(
			$this->namespace,
			'/license/network',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_network_license' ),
				'permission_callback' => array( $this, 'network_permissions_check' ),
			)
		);

		// POST /license/network/broadcast - Broadcast license to sites (multisite).
		register_rest_route(
			$this->namespace,
			'/license/network/broadcast',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'broadcast_license' ),
				'permission_callback' => array( $this, 'network_permissions_check' ),
				'args'                => array(
					'license_key' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'site_ids'    => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'integer',
						),
					),
					'auto_new'    => array(
						'type'              => 'boolean',
						'default'           => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
				),
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
	 * Check network permissions
	 *
	 * @return bool|WP_Error
	 */
	public function network_permissions_check() {
		if ( ! is_multisite() ) {
			return $this->error_response(
				'not_multisite',
				__( 'Network endpoints are only available in multisite installations.', 'plugin-wpshadow' ),
				400
			);
		}

		return $this->check_permission( 'manage_network_options' );
	}

	/**
	 * Get license status
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_license( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_License' ) ) {
			return $this->error_response(
				'license_not_available',
				__( 'License system is not available.', 'plugin-wpshadow' ),
				503
			);
		}

		$state = WPSHADOW_License::get_state( false );

		return $this->success_response( $state );
	}

	/**
	 * Register license key
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function register_license( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_License' ) ) {
			return $this->error_response(
				'license_not_available',
				__( 'License system is not available.', 'plugin-wpshadow' ),
				503
			);
		}

		// Check rate limit.
		$rate_check = $this->check_rate_limit( 'register_license', 5, 300 );
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}

		$license_key = $request->get_param( 'license_key' );

		if ( empty( $license_key ) ) {
			return $this->error_response(
				'invalid_license_key',
				__( 'License key cannot be empty.', 'plugin-wpshadow' ),
				400
			);
		}

		// Store license key.
		$result = WPSHADOW_License::register( $license_key, false );

		if ( is_wp_error( $result ) ) {
			return $this->error_response(
				'registration_failed',
				$result->get_error_message(),
				400
			);
		}

		return $this->success_response(
			array( 'status' => 'registered' ),
			__( 'License registered successfully.', 'plugin-wpshadow' ),
			201
		);
	}

	/**
	 * Remove license
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function remove_license( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_License' ) ) {
			return $this->error_response(
				'license_not_available',
				__( 'License system is not available.', 'plugin-wpshadow' ),
				503
			);
		}

		$result = WPSHADOW_License::remove( false );

		if ( ! $result ) {
			return $this->error_response(
				'removal_failed',
				__( 'Couldn\'t remove the license.', 'plugin-wpshadow' ),
				500
			);
		}

		return $this->success_response(
			array( 'status' => 'removed' ),
			__( 'License removed successfully.', 'plugin-wpshadow' )
		);
	}

	/**
	 * Verify license
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function verify_license( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_License' ) ) {
			return $this->error_response(
				'license_not_available',
				__( 'License system is not available.', 'plugin-wpshadow' ),
				503
			);
		}

		// Check rate limit.
		$rate_check = $this->check_rate_limit( 'verify_license', 10, 300 );
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}

		$result = WPSHADOW_License::verify_remote( false );

		if ( is_wp_error( $result ) ) {
			return $this->error_response(
				'verification_failed',
				$result->get_error_message(),
				400
			);
		}

		$state = WPSHADOW_License::get_state( false );

		return $this->success_response(
			$state,
			__( 'License verified successfully.', 'plugin-wpshadow' )
		);
	}

	/**
	 * Get network license status
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_network_license( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_License' ) ) {
			return $this->error_response(
				'license_not_available',
				__( 'License system is not available.', 'plugin-wpshadow' ),
				503
			);
		}

		$state = WPSHADOW_License::get_state( true );

		$sites = array();
		if ( is_multisite() ) {
			$sites_list = get_sites( array( 'number' => 1000 ) );
			foreach ( $sites_list as $site ) {
				switch_to_blog( $site->blog_id );
				$site_state = WPSHADOW_License::get_state( false );
				restore_current_blog();

				$sites[] = array(
					'site_id' => $site->blog_id,
					'domain'  => $site->domain,
					'path'    => $site->path,
					'status'  => $site_state,
				);
			}
		}

		return $this->success_response(
			array(
				'network' => $state,
				'sites'   => $sites,
			)
		);
	}

	/**
	 * Broadcast license to sites
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function broadcast_license( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_License' ) ) {
			return $this->error_response(
				'license_not_available',
				__( 'License system is not available.', 'plugin-wpshadow' ),
				503
			);
		}

		// Check rate limit.
		$rate_check = $this->check_rate_limit( 'broadcast_license', 3, 600 );
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}

		$license_key = $request->get_param( 'license_key' );
		$site_ids    = $request->get_param( 'site_ids' );
		$auto_new    = $request->get_param( 'auto_new' );

		if ( empty( $license_key ) ) {
			return $this->error_response(
				'invalid_license_key',
				__( 'License key cannot be empty.', 'plugin-wpshadow' ),
				400
			);
		}

		$result = WPSHADOW_License::broadcast_network_key( $license_key, $site_ids, $auto_new );

		return $this->success_response(
			$result,
			sprintf(
				/* translators: 1: successful count, 2: failed count */
				__( 'Broadcast completed: %1$d sites successful, %2$d failed.', 'plugin-wpshadow' ),
				$result['success'],
				$result['failed']
			)
		);
	}
}
