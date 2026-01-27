<?php
/**
 * Diagnostic: REST API Authentication
 *
 * Checks if authenticated REST API access works for the current session.
 * If not authenticated, warns that auth could not be validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\API
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Rest_Api_Auth_Test
 *
 * Tests REST API authentication.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Api_Auth_Test extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-auth-test';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST API Authentication';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if authenticated REST API access works';

	/**
	 * Check REST API authentication for current session.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$me_url   = rest_url( 'wp/v2/users/me' );
		$response = wp_remote_get( $me_url );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST API authentication test failed because the request could not be completed.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_api_auth_test',
				'meta'        => array(
					'endpoint'      => $me_url,
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$status = wp_remote_retrieve_response_code( $response );

		if ( 401 === $status || 403 === $status ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST API authentication could not be validated for the current session (401/403). If you expect authenticated requests (logged-in session or application passwords), ensure nonces or credentials are provided.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_api_auth_test',
				'meta'        => array(
					'endpoint'    => $me_url,
					'http_status' => $status,
				),
			);
		}

		return null;
	}
}
