<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: REST API Unrestricted
 *
 * Detects when WordPress REST API is unrestricted or too permissive.
 * Unrestricted REST API can expose sensitive site information.
 *
 * @since 1.2.0
 */
class Test_Rest_Api_Unrestricted extends Diagnostic_Base {


	/**
	 * Check for REST API restrictions
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$issues = self::detect_rest_issues();

		if ( empty( $issues ) ) {
			return null;
		}

		$threat = count( $issues ) * 15;
		$threat = min( 60, $threat );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'yellow',
			'passed'        => false,
			'issue'         => sprintf(
				'Found %d REST API security issues',
				count( $issues )
			),
			'metadata'      => array(
				'issues_count' => count( $issues ),
				'issues'       => $issues,
			),
			'kb_link'       => 'https://wpshadow.com/kb/rest-api-security/',
			'training_link' => 'https://wpshadow.com/training/wordpress-api-hardening/',
		);
	}

	/**
	 * Guardian Sub-Test: REST API access
	 *
	 * @return array Test result
	 */
	public static function test_rest_api_access(): array {
		$rest_enabled = rest_get_server() !== null;
		$rest_url     = rest_url();

		return array(
			'test_name'    => 'REST API Access',
			'rest_enabled' => $rest_enabled,
			'rest_url'     => $rest_url,
			'description'  => $rest_enabled ? 'REST API is enabled' : 'REST API is disabled',
		);
	}

	/**
	 * Guardian Sub-Test: User enumeration via REST
	 *
	 * @return array Test result
	 */
	public static function test_user_enumeration(): array {
		$users_endpoint = rest_url( '/wp/v2/users' );
		$can_enumerate  = self::can_enumerate_users( $users_endpoint );

		return array(
			'test_name'   => 'User Enumeration via REST',
			'endpoint'    => $users_endpoint,
			'enumerable'  => $can_enumerate,
			'passed'      => ! $can_enumerate,
			'description' => $can_enumerate ? 'Users can be enumerated (security risk)' : 'User enumeration protected',
		);
	}

	/**
	 * Guardian Sub-Test: Post exposure via REST
	 *
	 * @return array Test result
	 */
	public static function test_post_exposure(): array {
		$posts_endpoint = rest_url( '/wp/v2/posts' );
		$public_posts   = self::get_public_rest_posts( $posts_endpoint );

		return array(
			'test_name'      => 'Post Exposure via REST',
			'posts_endpoint' => $posts_endpoint,
			'public_posts'   => count( $public_posts ),
			'description'    => sprintf( '%d posts available via REST', count( $public_posts ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Authentication requirements
	 *
	 * @return array Test result
	 */
	public static function test_authentication_requirements(): array {
		$requires_auth = self::rest_requires_authentication();

		return array(
			'test_name'     => 'REST Authentication',
			'requires_auth' => $requires_auth,
			'passed'        => $requires_auth,
			'description'   => $requires_auth ? 'Sensitive endpoints require authentication' : 'Some endpoints may not require authentication',
		);
	}

	/**
	 * Guardian Sub-Test: Endpoint discovery
	 *
	 * @return array Test result
	 */
	public static function test_endpoint_discovery(): array {
		$root_endpoint   = rest_url( '/' );
		$discoverable    = self::is_root_endpoint_discoverable( $root_endpoint );
		$endpoints_count = self::count_available_endpoints();

		return array(
			'test_name'           => 'Endpoint Discovery',
			'root_discoverable'   => $discoverable,
			'available_endpoints' => $endpoints_count,
			'description'         => $discoverable ? sprintf( '%d endpoints discoverable', $endpoints_count ) : 'Root endpoint hidden',
		);
	}

	/**
	 * Detect REST API security issues
	 *
	 * @return array List of issues
	 */
	private static function detect_rest_issues(): array {
		$issues = array();

		// Check if users can be enumerated
		if ( self::can_enumerate_users( rest_url( '/wp/v2/users' ) ) ) {
			$issues[] = 'User enumeration possible via REST API';
		}

		// Check if posts are exposed
		if ( self::get_public_rest_posts( rest_url( '/wp/v2/posts' ) ) ) {
			$issues[] = 'Posts are publicly accessible via REST API';
		}

		// Check if root endpoint is discoverable
		if ( self::is_root_endpoint_discoverable( rest_url( '/' ) ) ) {
			$issues[] = 'REST API root endpoint is publicly discoverable';
		}

		return $issues;
	}

	/**
	 * Check if users can be enumerated
	 *
	 * @param string $endpoint Users endpoint
	 * @return bool
	 */
	private static function can_enumerate_users( string $endpoint ): bool {
		try {
			$response = wp_remote_get( $endpoint );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$status = wp_remote_retrieve_response_code( $response );
			return $status === 200;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Get public REST posts
	 *
	 * @param string $endpoint Posts endpoint
	 * @return array Posts
	 */
	private static function get_public_rest_posts( string $endpoint ): array {
		try {
			$response = wp_remote_get( $endpoint . '?_embed=1&per_page=5' );

			if ( is_wp_error( $response ) ) {
				return array();
			}

			$body  = wp_remote_retrieve_body( $response );
			$posts = json_decode( $body, true );

			return is_array( $posts ) ? array_slice( $posts, 0, 5 ) : array();
		} catch ( \Exception $e ) {
			return array();
		}
	}

	/**
	 * Check if REST requires authentication
	 *
	 * @return bool
	 */
	private static function rest_requires_authentication(): bool {
		// Check if edit endpoints require auth
		$edit_endpoint = rest_url( '/wp/v2/posts' );

		try {
			$response = wp_remote_post( $edit_endpoint, array( 'blocking' => false ) );
			$status   = wp_remote_retrieve_response_code( $response );

			// If POST returns 401 or 403, auth is required
			return in_array( $status, array( 401, 403 ), true );
		} catch ( \Exception $e ) {
			return true; // Assume auth required if error
		}
	}

	/**
	 * Check if REST root endpoint is discoverable
	 *
	 * @param string $endpoint Root endpoint
	 * @return bool
	 */
	private static function is_root_endpoint_discoverable( string $endpoint ): bool {
		try {
			$response = wp_remote_get( $endpoint );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$status = wp_remote_retrieve_response_code( $response );
			return $status === 200;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Count available REST endpoints
	 *
	 * @return int
	 */
	private static function count_available_endpoints(): int {
		try {
			$response = wp_remote_get( rest_url( '/' ) );

			if ( is_wp_error( $response ) ) {
				return 0;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			return count( $data['routes'] ?? array() );
		} catch ( \Exception $e ) {
			return 0;
		}
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'REST API Unrestricted';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Checks if REST API has proper security restrictions';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}
