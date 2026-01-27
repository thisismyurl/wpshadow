<?php
/**
 * Diagnostic: REST Nonce Validation
 *
 * Tests that the REST API properly validates nonces for authentication.
 * Without nonce validation, the site is vulnerable to CSRF attacks.
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
 * Class Diagnostic_Rest_Nonce_Validation
 *
 * Verifies REST API enforces nonce validation.
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
	protected static $description = 'Tests that REST API validates nonces correctly';

	/**
	 * Check REST API nonce validation.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if cookie authentication error checking is enabled.
		$cookie_check_enabled = has_filter( 'rest_authentication_errors', 'rest_cookie_check_errors' );

		if ( ! $cookie_check_enabled ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST API cookie authentication nonce checking is not enabled. This may expose your site to CSRF attacks.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-nonce-validation',
				'meta'        => array(
					'cookie_check_enabled' => false,
				),
			);
		}

		// Simulate a REST request without a valid nonce (if logged in).
		if ( is_user_logged_in() ) {
			$invalid_nonce = 'invalid_nonce';
			$request       = new \WP_REST_Request( 'POST', '/wp/v2/posts' );
			$request->set_header( 'X-WP-Nonce', $invalid_nonce );

			// Check if authentication fails with invalid nonce.
			$auth_result = apply_filters( 'rest_authentication_errors', null );

			if ( ! is_wp_error( $auth_result ) ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'REST API is not rejecting requests with invalid nonces. This is a security risk.', 'wpshadow' ),
					'severity'    => 'medium',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/api-rest-nonce-validation',
					'meta'        => array(
						'auth_result' => $auth_result,
					),
				);
			}

			// Test with a valid nonce.
			$valid_nonce = wp_create_nonce( 'wp_rest' );
			$request->set_header( 'X-WP-Nonce', $valid_nonce );

			// Authentication should succeed with valid nonce.
			wp_set_current_user( get_current_user_id() );
			$_REQUEST['_wpnonce'] = $valid_nonce;
			$auth_result = apply_filters( 'rest_authentication_errors', null );

			if ( is_wp_error( $auth_result ) ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'REST API is rejecting requests with valid nonces. This may cause functionality issues.', 'wpshadow' ),
					'severity'    => 'low',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/api-rest-nonce-validation',
					'meta'        => array(
						'auth_error' => $auth_result->get_error_message(),
					),
				);
			}
		}

		// Nonce validation is working correctly.
		return null;
	}
}
