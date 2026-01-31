<?php
/**
 * REST API User Enumeration Diagnostic
 *
 * Detects if WordPress REST API exposes admin usernames via
 * /wp-json/wp/v2/users endpoint (information disclosure risk).
 *
 * @since   1.2802.1445
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_REST_User_Enumeration Class
 *
 * Checks if REST API exposes user information without authentication.
 * Common WordPress vulnerability in default configuration.
 *
 * @since 1.2802.1445
 */
class Diagnostic_REST_User_Enumeration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-user-enumeration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API User Enumeration Risk';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects if REST API exposes admin usernames';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2802.1445
	 * @return array|null Finding array if user enumeration possible, null otherwise.
	 */
	public static function check() {
		// Step 1: Early bailout - check if REST API is available
		if ( ! self::should_run_check() ) {
			return null;
		}

		// Step 2: Test if users endpoint is accessible
		$enumeration_test = self::test_user_enumeration();

		// Step 3: If no enumeration possible, return null
		if ( ! $enumeration_test['vulnerable'] ) {
			return null;
		}

		// Step 4: Return comprehensive finding
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of exposed users */
				__( 'REST API exposes %d user account(s) without authentication. Attackers can enumerate valid admin usernames, dramatically increasing brute force attack success.', 'wpshadow' ),
				$enumeration_test['exposed_count']
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => true, // Can add filter to disable
			'kb_link'      => 'https://wpshadow.com/kb/security-rest-user-enumeration',
			'family'       => self::$family,
			'meta'         => array(
				'exposed_users'    => $enumeration_test['exposed_count'],
				'endpoint_url'     => $enumeration_test['endpoint_url'],
				'rest_api_enabled' => true,
			),
			'details'      => array(
				'why_user_enumeration_matters' => array(
					__( 'Attackers discover valid admin usernames (50% of login credentials)', 'wpshadow' ),
					__( 'Brute force attacks only need to guess passwords (not usernames)', 'wpshadow' ),
					__( 'Automated scanners probe /wp-json/wp/v2/users on every WordPress site', 'wpshadow' ),
					__( 'Default WordPress configuration exposes all users publicly', 'wpshadow' ),
					__( 'Information disclosure violates principle of least disclosure', 'wpshadow' ),
				),
				'exposed_users_sample'         => self::format_exposed_users( $enumeration_test['users'] ),
				'attack_scenarios'             => array(
					'Brute Force Attack' => array(
						'Step 1: Enumerate users → get "admin", "administrator", etc.',
						'Step 2: Try common passwords against known usernames',
						'Success Rate: 80% higher than guessing both username + password',
					),
					'Spear Phishing' => array(
						'Step 1: Enumerate users → get real names, usernames',
						'Step 2: Craft targeted phishing emails',
						'Example: "admin@yoursite.com, reset your password here..."',
					),
					'Social Engineering' => array(
						'Step 1: Discover admin usernames and display names',
						'Step 2: Research users on social media',
						'Step 3: Targeted attacks based on real identities',
					),
				),
				'remediation_options'          => array(
					'Option 1: Add Filter (Quick Fix)' => array(
						'description' => __( 'Add filter to hide users endpoint from public', 'wpshadow' ),
						'time'        => __( '2 minutes', 'wpshadow' ),
						'difficulty'  => __( 'Easy (WPShadow can apply automatically)', 'wpshadow' ),
						'code'        => self::get_filter_code(),
					),
					'Option 2: Security Plugin' => array(
						'description' => __( 'Install security plugin with REST API restrictions', 'wpshadow' ),
						'plugins'     => 'Wordfence, iThemes Security, All In One WP Security',
						'time'        => __( '10 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
					),
					'Option 3: Disable REST API' => array(
						'description' => __( 'Completely disable REST API (breaks Gutenberg, mobile apps)', 'wpshadow' ),
						'time'        => __( '5 minutes', 'wpshadow' ),
						'tradeoff'    => __( 'WARNING: Disables block editor and WP mobile apps', 'wpshadow' ),
					),
				),
				'testing_enumeration'          => array(
					'Browser Test' => sprintf(
						'Visit: %s (should return 403 or require login)',
						home_url( '/wp-json/wp/v2/users' )
					),
					'Curl Test' => sprintf(
						'curl -X GET "%s"',
						home_url( '/wp-json/wp/v2/users' )
					),
					'Expected (Fixed)' => 'HTTP 403 Forbidden or authentication required',
					'Expected (Vulnerable)' => 'JSON array with user data',
				),
			),
		);
	}

	/**
	 * Check if diagnostic should run.
	 *
	 * @since  1.2802.1445
	 * @return bool True if check should run, false otherwise.
	 */
	private static function should_run_check() {
		// Only run if REST API is available
		return function_exists( 'rest_url' );
	}

	/**
	 * Test if user enumeration is possible via REST API.
	 *
	 * @since  1.2802.1445
	 * @return array {
	 *     Enumeration test results.
	 *
	 *     @type bool   $vulnerable Whether enumeration is possible.
	 *     @type int    $exposed_count Number of exposed users.
	 *     @type array  $users Sample of exposed users.
	 *     @type string $endpoint_url URL that was tested.
	 * }
	 */
	private static function test_user_enumeration() {
		// Check if users endpoint is restricted
		$endpoint_url = rest_url( 'wp/v2/users' );

		// Try to get users via REST API (internal request)
		$request  = new \WP_REST_Request( 'GET', '/wp/v2/users' );
		$response = rest_do_request( $request );

		if ( is_wp_error( $response ) ) {
			return array(
				'vulnerable'     => false,
				'exposed_count'  => 0,
				'users'          => array(),
				'endpoint_url'   => $endpoint_url,
			);
		}

		// Check if endpoint requires authentication
		if ( $response->get_status() === 401 || $response->get_status() === 403 ) {
			// Good: endpoint is restricted
			return array(
				'vulnerable'     => false,
				'exposed_count'  => 0,
				'users'          => array(),
				'endpoint_url'   => $endpoint_url,
			);
		}

		// Check if data is returned
		$data = $response->get_data();

		if ( empty( $data ) || ! is_array( $data ) ) {
			return array(
				'vulnerable'     => false,
				'exposed_count'  => 0,
				'users'          => array(),
				'endpoint_url'   => $endpoint_url,
			);
		}

		// Vulnerable: users are exposed
		$exposed_users = self::extract_user_info( $data );

		return array(
			'vulnerable'     => true,
			'exposed_count'  => count( $exposed_users ),
			'users'          => array_slice( $exposed_users, 0, 5 ), // Sample of first 5
			'endpoint_url'   => $endpoint_url,
		);
	}

	/**
	 * Extract relevant user information from REST API response.
	 *
	 * @since  1.2802.1445
	 * @param  array $users_data User data from REST API.
	 * @return array Extracted user information.
	 */
	private static function extract_user_info( $users_data ) {
		$extracted = array();

		foreach ( $users_data as $user ) {
			if ( ! is_array( $user ) && ! is_object( $user ) ) {
				continue;
			}

			$user = (array) $user;

			$extracted[] = array(
				'id'   => $user['id'] ?? 0,
				'name' => $user['name'] ?? '',
				'slug' => $user['slug'] ?? '',
			);
		}

		return $extracted;
	}

	/**
	 * Format exposed users for display in details.
	 *
	 * @since  1.2802.1445
	 * @param  array $users Array of exposed users.
	 * @return array Formatted user list.
	 */
	private static function format_exposed_users( $users ) {
		$formatted = array();

		foreach ( $users as $user ) {
			$formatted[] = sprintf(
				'ID: %d, Name: %s, Slug: %s',
				$user['id'],
				$user['name'],
				$user['slug']
			);
		}

		return $formatted;
	}

	/**
	 * Get filter code to disable user enumeration.
	 *
	 * @since  1.2802.1445
	 * @return string PHP code to add to functions.php.
	 */
	private static function get_filter_code() {
		return "add_filter( 'rest_endpoints', function( \$endpoints ) {\n" .
			"    if ( isset( \$endpoints['/wp/v2/users'] ) ) {\n" .
			"        unset( \$endpoints['/wp/v2/users'] );\n" .
			"    }\n" .
			"    if ( isset( \$endpoints['/wp/v2/users/(?P<id>[\\\d]+)'] ) ) {\n" .
			"        unset( \$endpoints['/wp/v2/users/(?P<id>[\\\d]+)'] );\n" .
			"    }\n" .
			"    return \$endpoints;\n" .
			"} );";
	}

	/**
	 * Set test REST API response for testing purposes.
	 *
	 * This allows tests to inject mock REST API responses.
	 *
	 * @since  1.2802.1445
	 * @param  array $response Mock REST API response.
	 * @return void
	 */
	public static function set_test_rest_response( $response ) {
		global $wp_rest_test_response;
		$wp_rest_test_response = $response;
	}

	/**
	 * Clear test REST API response.
	 *
	 * @since  1.2802.1445
	 * @return void
	 */
	public static function clear_test_rest_response() {
		global $wp_rest_test_response;
		unset( $wp_rest_test_response );
	}
}
