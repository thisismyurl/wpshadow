<?php
/**
 * Base REST API Controller
 *
 * @package wp_support_SUPPORT
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport\API;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base REST Controller providing common functionality
 */
abstract class WPS_REST_Controller_Base extends WP_REST_Controller {

	/**
	 * Namespace for all REST routes
	 *
	 * @var string
	 */
	protected $namespace = 'timu/v1';

	/**
	 * Check if user has required capability
	 *
	 * @param string $capability Required capability.
	 * @return bool|WP_Error
	 */
	protected function check_permission( string $capability ) {
		if ( ! current_user_can( $capability ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access this resource.', 'plugin-wp-support-thisismyurl' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Create standardized success response
	 *
	 * @param mixed  $data    Response data.
	 * @param string $message Success message.
	 * @param int    $status  HTTP status code.
	 * @return WP_REST_Response
	 */
	protected function success_response( $data, string $message = '', int $status = 200 ): WP_REST_Response {
		$response = array(
			'success' => true,
			'data'    => $data,
		);

		if ( ! empty( $message ) ) {
			$response['message'] = $message;
		}

		return new WP_REST_Response( $response, $status );
	}

	/**
	 * Create standardized error response
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @param int    $status  HTTP status code.
	 * @param array  $data    Additional error data.
	 * @return WP_Error
	 */
	protected function error_response( string $code, string $message, int $status = 400, array $data = array() ): WP_Error {
		return new WP_Error(
			$code,
			$message,
			array_merge( array( 'status' => $status ), $data )
		);
	}

	/**
	 * Validate and sanitize slug parameter
	 *
	 * @param string $slug Slug to validate.
	 * @return string|WP_Error
	 */
	protected function validate_slug( string $slug ) {
		$sanitized = sanitize_key( $slug );

		if ( empty( $sanitized ) ) {
			return $this->error_response(
				'invalid_slug',
				__( 'Invalid slug parameter.', 'plugin-wp-support-thisismyurl' ),
				400
			);
		}

		return $sanitized;
	}

	/**
	 * Check rate limit for expensive operations
	 *
	 * @param string $operation Operation identifier.
	 * @param int    $limit     Maximum requests per period.
	 * @param int    $period    Time period in seconds.
	 * @return bool|WP_Error
	 */
	protected function check_rate_limit( string $operation, int $limit = 10, int $period = 300 ) {
		$user_id = get_current_user_id();
		$key     = "wps_rate_limit_{$operation}_{$user_id}";
		$count   = get_transient( $key );

		if ( false === $count ) {
			set_transient( $key, 1, $period );
			return true;
		}

		if ( $count >= $limit ) {
			return $this->error_response(
				'rate_limit_exceeded',
				sprintf(
					/* translators: 1: operation name, 2: limit, 3: period in minutes */
					__( 'Rate limit exceeded for %1$s. Maximum %2$d requests per %3$d minutes.', 'plugin-wp-support-thisismyurl' ),
					$operation,
					$limit,
					$period / 60
				),
				429
			);
		}

		set_transient( $key, $count + 1, $period );
		return true;
	}

	/**
	 * Get pagination parameters from request
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array Array with page, per_page, and offset.
	 */
	protected function get_pagination_params( WP_REST_Request $request ): array {
		$page     = absint( $request->get_param( 'page' ) ?: 1 );
		$per_page = absint( $request->get_param( 'per_page' ) ?: 20 );
		$per_page = min( $per_page, 100 ); // Cap at 100 items per page.
		$offset   = ( $page - 1 ) * $per_page;

		return array(
			'page'     => $page,
			'per_page' => $per_page,
			'offset'   => $offset,
		);
	}

	/**
	 * Add pagination headers to response
	 *
	 * @param WP_REST_Response $response  Response object.
	 * @param int              $total     Total items.
	 * @param int              $page      Current page.
	 * @param int              $per_page  Items per page.
	 * @return WP_REST_Response
	 */
	protected function add_pagination_headers( WP_REST_Response $response, int $total, int $page, int $per_page ): WP_REST_Response {
		$total_pages = ceil( $total / $per_page );

		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', $total_pages );

		return $response;
	}
}
