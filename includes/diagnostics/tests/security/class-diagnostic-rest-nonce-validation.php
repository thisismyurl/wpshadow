<?php
/**
 * Diagnostic: REST Nonce Validation
 *
 * Validates that REST nonces authenticate access to protected endpoints.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Rest_Nonce_Validation
 *
 * Tests REST nonce validation for authenticated requests.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Nonce_Validation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-nonce-validation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST Nonce Validation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST nonce authentication on protected endpoints';

	/**
	 * Check REST nonce validation on a protected route.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_user_logged_in() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST nonce validation cannot be tested because no user is logged in. Test during an authenticated session.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_nonce_validation',
				'meta'        => array(
					'is_logged_in' => false,
				),
			);
		}

		$nonce = wp_create_nonce( 'wp_rest' );
		$me    = rest_url( 'wp/v2/users/me' );
		$response = wp_remote_get( $me, array( 'headers' => array( 'X-WP-Nonce' => $nonce ) ) );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST nonce validation test failed because the request could not be completed.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_nonce_validation',
				'meta'        => array(
					'endpoint'      => $me,
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$status = wp_remote_retrieve_response_code( $response );

		if ( 401 === $status || 403 === $status ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST nonce validation failed (401/403). Ensure nonces are correctly passed in X-WP-Nonce and REST cookies are valid.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_nonce_validation',
				'meta'        => array(
					'endpoint'    => $me,
					'http_status' => $status,
				),
			);
		}

		return null;
	}
}
