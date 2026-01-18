<?php
/**
 * Publishing Assistant REST API Endpoints
 *
 * Provides REST endpoints for the publishing assistant functionality.
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Publishing_Assistant_API {
	/**
	 * Initialize API endpoints.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		// Review endpoint
		register_rest_route(
			'wpshadow/v1',
			'/publishing-assistant/review',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_run_review' ),
				'permission_callback' => array( __CLASS__, 'check_review_permission' ),
			)
		);

		// Callback endpoint
		register_rest_route(
			'wpshadow/v1',
			'/publishing-assistant/callback',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_review_callback' ),
				'permission_callback' => array( __CLASS__, 'check_review_permission' ),
			)
		);
	}

	/**
	 * Check permission for review endpoints.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool True if user can edit posts.
	 */
	public static function check_review_permission( \WP_REST_Request $request ): bool {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Handle review REST request.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response object.
	 */
	public static function rest_run_review( \WP_REST_Request $request ) {
		$post_id = $request->get_param( 'post_id' );
		$post    = get_post( (int) $post_id );

		if ( ! $post ) {
			return new \WP_Error(
				'post_not_found',
				__( 'Post not found.', 'wpshadow' ),
				array( 'status' => 404 )
			);
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return new \WP_Error(
				'insufficient_permission',
				__( 'You cannot edit this post.', 'wpshadow' ),
				array( 'status' => 403 )
			);
		}

		$reviews = WPSHADOW_Publishing_Assistant::run_all_reviews( $post );

		return new \WP_REST_Response(
			array(
				'reviews' => $reviews,
			),
			200
		);
	}

	/**
	 * Handle review callback REST request.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response object.
	 */
	public static function rest_review_callback( \WP_REST_Request $request ) {
		$action_type = $request->get_param( 'action_type' );
		$reviewer_id = $request->get_param( 'reviewer_id' );
		$post_id     = $request->get_param( 'post_id' );

		/**
		 * Allow features to handle publishing assistant callbacks via REST.
		 *
		 * @param string $action_type Type of action.
		 * @param string $reviewer_id Reviewer ID.
		 * @param int    $post_id     Post ID.
		 */
		do_action( 'wpshadow_publishing_assistant_callback', $action_type, $reviewer_id, (int) $post_id );

		return new \WP_REST_Response(
			array(
				'success' => true,
			),
			200
		);
	}
}
