<?php
/**
 * REST API Sensitive Routes Protected Diagnostic
 *
 * Tests whether the WordPress REST API users endpoint exposes user account
 * data without authentication, enabling username enumeration attacks.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Rest_Api_Sensitive_Routes_Protected Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Rest_Api_Sensitive_Routes_Protected extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-sensitive-routes-protected';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST API Sensitive Routes Protected';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the /wp-json/wp/v2/users endpoint is publicly accessible without authentication, which would allow attackers to enumerate valid usernames on the site.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Makes an unauthenticated GET request to the /wp/v2/users REST endpoint.
	 * A HTTP 200 response containing an array of user objects indicates the
	 * endpoint is open and username enumeration is possible.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// Check if the users endpoint is accessible without authentication.
		// WordPress 5.7+ requires authentication for the /wp/v2/users index,
		// but some configurations or plugins may loosen this.
		$rest_url = rest_url( 'wp/v2/users' );
		$response = wp_remote_get( $rest_url, array(
			'timeout'    => 5,
			'user-agent' => 'WPShadow-Diagnostic/1.0',
			'sslverify'  => false,
		) );

		if ( is_wp_error( $response ) ) {
			return null; // Cannot test; skip to avoid false positives.
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// A 200 response with an array of users means enumeration is possible.
		if ( 200 === (int) $code && is_array( $data ) && ! empty( $data ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The WordPress REST API users endpoint (/wp-json/wp/v2/users) is publicly accessible and returned user account data. This allows attackers to enumerate valid usernames, which aids brute-force attacks. Restrict this endpoint using a security plugin such as iThemes Security or by filtering rest_endpoints with a permission callback that requires authentication.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'kb_link'      => '',
				'details'      => array(
					'endpoint'      => '/wp/v2/users',
					'http_code'     => $code,
					'user_count'    => count( $data ),
				),
			);
		}

		return null;
	}
}
