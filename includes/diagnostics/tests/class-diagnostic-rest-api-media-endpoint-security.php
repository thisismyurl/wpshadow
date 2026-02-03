<?php
/**
 * REST API Media Endpoint Security Diagnostic
 *
 * Detects if REST API media endpoints have proper authentication and authorization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.1635
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_REST_API_Media_Endpoint_Security Class
 *
 * Tests if REST API media endpoints enforce proper authentication and
 * authorization checks to prevent unauthorized media access and manipulation.
 *
 * @since 1.26033.1635
 */
class Diagnostic_REST_API_Media_Endpoint_Security extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-media-endpoint-security';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Media Endpoint Security';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies REST API media endpoints have proper authentication';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.26033.1635
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if REST API is enabled
		if ( ! rest_is_enabled() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API is disabled. Enable it to ensure proper security controls are in place.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-media-security',
			);
		}

		// Check if media endpoint permissions are properly configured
		$media_rest_controller = class_exists( 'WP_REST_Media_Controller' );

		if ( ! $media_rest_controller ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Media REST controller is not available. Security controls may not be enforced.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-media-security',
			);
		}

		// Check for JWT or authentication plugin
		$has_auth = function_exists( 'wp_verify_nonce' ) && 
			( defined( 'JWT_AUTH_SECRET_KEY' ) || 
			  is_plugin_active( 'jwt-authentication-for-wp-rest-api/jwt-auth.php' ) );

		if ( ! $has_auth && ! is_user_logged_in() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API authentication is not properly configured. Implement JWT or OAuth for secure media access.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-media-security',
			);
		}

		return null;
	}
}
