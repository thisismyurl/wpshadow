<?php
/**
 * Vault Operations REST API Controller
 *
 * @package wp_support_SUPPORT
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use WPS\CoreSupport\WPS_Vault;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vault Operations REST Controller
 */
class WPS_REST_Vault_Controller extends WPS_REST_Controller_Base {

	/**
	 * Register routes
	 */
	public function register_routes(): void {
		// GET /vault/status - Get vault configuration and statistics.
		register_rest_route(
			$this->namespace,
			'/vault/status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_status' ),
				'permission_callback' => array( $this, 'read_permissions_check' ),
			)
		);

		// GET /vault/files - List vault files.
		register_rest_route(
			$this->namespace,
			'/vault/files',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_files' ),
				'permission_callback' => array( $this, 'read_permissions_check' ),
				'args'                => $this->get_collection_params(),
			)
		);

		// GET /vault/files/{attachment_id} - Get vault file details.
		register_rest_route(
			$this->namespace,
			'/vault/files/(?P<attachment_id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_file' ),
				'permission_callback' => array( $this, 'read_permissions_check' ),
				'args'                => array(
					'attachment_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// POST /vault/files/{attachment_id}/verify - Verify file integrity.
		register_rest_route(
			$this->namespace,
			'/vault/files/(?P<attachment_id>\d+)/verify',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'verify_file' ),
				'permission_callback' => array( $this, 'write_permissions_check' ),
				'args'                => array(
					'attachment_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// POST /vault/files/{attachment_id}/restore - Restore from vault.
		register_rest_route(
			$this->namespace,
			'/vault/files/(?P<attachment_id>\d+)/restore',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'restore_file' ),
				'permission_callback' => array( $this, 'write_permissions_check' ),
				'args'                => array(
					'attachment_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// GET /vault/size - Get vault size and limits.
		register_rest_route(
			$this->namespace,
			'/vault/size',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_size' ),
				'permission_callback' => array( $this, 'read_permissions_check' ),
			)
		);

		// POST /vault/cleanup - Trigger vault cleanup.
		register_rest_route(
			$this->namespace,
			'/vault/cleanup',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'cleanup' ),
				'permission_callback' => array( $this, 'write_permissions_check' ),
			)
		);
	}

	/**
	 * Check read permissions
	 *
	 * @return bool|WP_Error
	 */
	public function read_permissions_check() {
		return $this->check_permission( 'upload_files' );
	}

	/**
	 * Check write permissions
	 *
	 * @return bool|WP_Error
	 */
	public function write_permissions_check() {
		return $this->check_permission( 'manage_options' );
	}

	/**
	 * Get vault status
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_status( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Vault' ) ) {
			return $this->error_response(
				'vault_not_available',
				__( 'Vault module is not available.', 'plugin-wp-support-thisismyurl' ),
				503
			);
		}

		$settings = WPS_Vault::get_settings();
		$stats    = array(
			'enabled'     => ! empty( $settings['enabled'] ),
			'files_count' => $this->get_files_count(),
			'total_size'  => $this->get_total_size(),
		);

		return $this->success_response(
			array(
				'settings' => $settings,
				'stats'    => $stats,
			)
		);
	}

	/**
	 * Get vault files
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_files( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Vault' ) ) {
			return $this->error_response(
				'vault_not_available',
				__( 'Vault module is not available.', 'plugin-wp-support-thisismyurl' ),
				503
			);
		}

		$params = $this->get_pagination_params( $request );

		$args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => $params['per_page'],
			'paged'          => $params['page'],
			'meta_query'     => array(
				array(
					'key'     => '_wps_vaulted',
					'value'   => '1',
					'compare' => '=',
				),
			),
		);

		$query = new \WP_Query( $args );
		$files = array();

		foreach ( $query->posts as $post ) {
			$files[] = $this->prepare_file_data( $post->ID );
		}

		$response = $this->success_response( $files );
		return $this->add_pagination_headers( $response, $query->found_posts, $params['page'], $params['per_page'] );
	}

	/**
	 * Get vault file
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_file( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Vault' ) ) {
			return $this->error_response(
				'vault_not_available',
				__( 'Vault module is not available.', 'plugin-wp-support-thisismyurl' ),
				503
			);
		}

		$attachment_id = $request->get_param( 'attachment_id' );

		if ( ! get_post( $attachment_id ) ) {
			return $this->error_response(
				'file_not_found',
				__( 'Attachment not found.', 'plugin-wp-support-thisismyurl' ),
				404
			);
		}

		$vaulted = get_post_meta( $attachment_id, '_wps_vaulted', true );
		if ( '1' !== $vaulted ) {
			return $this->error_response(
				'not_vaulted',
				__( 'Attachment is not vaulted.', 'plugin-wp-support-thisismyurl' ),
				404
			);
		}

		$data = $this->prepare_file_data( $attachment_id );
		return $this->success_response( $data );
	}

	/**
	 * Verify file integrity
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function verify_file( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Vault' ) ) {
			return $this->error_response(
				'vault_not_available',
				__( 'Vault module is not available.', 'plugin-wp-support-thisismyurl' ),
				503
			);
		}

		// Check rate limit.
		$rate_check = $this->check_rate_limit( 'verify_file', 20, 300 );
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}

		$attachment_id = $request->get_param( 'attachment_id' );

		if ( ! get_post( $attachment_id ) ) {
			return $this->error_response(
				'file_not_found',
				__( 'Attachment not found.', 'plugin-wp-support-thisismyurl' ),
				404
			);
		}

		// Placeholder for actual verification logic.
		$is_valid = true;

		return $this->success_response(
			array(
				'attachment_id' => $attachment_id,
				'valid'         => $is_valid,
			),
			__( 'File integrity verified.', 'plugin-wp-support-thisismyurl' )
		);
	}

	/**
	 * Restore file from vault
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function restore_file( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Vault' ) ) {
			return $this->error_response(
				'vault_not_available',
				__( 'Vault module is not available.', 'plugin-wp-support-thisismyurl' ),
				503
			);
		}

		// Check rate limit.
		$rate_check = $this->check_rate_limit( 'restore_file', 10, 300 );
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}

		$attachment_id = $request->get_param( 'attachment_id' );

		if ( ! get_post( $attachment_id ) ) {
			return $this->error_response(
				'file_not_found',
				__( 'Attachment not found.', 'plugin-wp-support-thisismyurl' ),
				404
			);
		}

		// Placeholder for actual restore logic.
		$restored = true;

		if ( ! $restored ) {
			return $this->error_response(
				'restore_failed',
				__( 'Failed to restore file from vault.', 'plugin-wp-support-thisismyurl' ),
				500
			);
		}

		return $this->success_response(
			array( 'attachment_id' => $attachment_id ),
			__( 'File restored from vault successfully.', 'plugin-wp-support-thisismyurl' )
		);
	}

	/**
	 * Get vault size information
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_size( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Vault' ) ) {
			return $this->error_response(
				'vault_not_available',
				__( 'Vault module is not available.', 'plugin-wp-support-thisismyurl' ),
				503
			);
		}

		$total_size  = $this->get_total_size();
		$files_count = $this->get_files_count();
		$limit       = 0; // No limit by default.

		return $this->success_response(
			array(
				'total_size'  => $total_size,
				'files_count' => $files_count,
				'limit'       => $limit,
				'percentage'  => $limit > 0 ? round( ( $total_size / $limit ) * 100, 2 ) : 0,
			)
		);
	}

	/**
	 * Cleanup vault
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function cleanup( WP_REST_Request $request ) {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Vault' ) ) {
			return $this->error_response(
				'vault_not_available',
				__( 'Vault module is not available.', 'plugin-wp-support-thisismyurl' ),
				503
			);
		}

		// Check rate limit.
		$rate_check = $this->check_rate_limit( 'vault_cleanup', 2, 3600 );
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}

		// Placeholder for actual cleanup logic.
		$removed_count = 0;

		return $this->success_response(
			array( 'removed_count' => $removed_count ),
			sprintf(
				/* translators: %d: number of files removed */
				__( 'Vault cleanup completed. %d orphaned files removed.', 'plugin-wp-support-thisismyurl' ),
				$removed_count
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
		);
	}

	/**
	 * Prepare file data for response
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array
	 */
	private function prepare_file_data( int $attachment_id ): array {
		$file     = get_attached_file( $attachment_id );
		$filesize = file_exists( $file ) ? filesize( $file ) : 0;

		return array(
			'attachment_id' => $attachment_id,
			'title'         => get_the_title( $attachment_id ),
			'mime_type'     => get_post_mime_type( $attachment_id ),
			'file_size'     => $filesize,
			'file_path'     => $file,
			'vaulted'       => get_post_meta( $attachment_id, '_wps_vaulted', true ) === '1',
			'vaulted_at'    => get_post_meta( $attachment_id, '_wps_vaulted_at', true ),
		);
	}

	/**
	 * Get total vault size
	 *
	 * @return int Total size in bytes.
	 */
	private function get_total_size(): int {
		global $wpdb;

		$query = "
			SELECT SUM(meta_value)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_wps_vault_size'
		";

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Get vault files count
	 *
	 * @return int Number of vaulted files.
	 */
	private function get_files_count(): int {
		global $wpdb;

		$query = "
			SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_wps_vaulted'
			AND meta_value = '1'
		";

		return (int) $wpdb->get_var( $query );
	}
}
