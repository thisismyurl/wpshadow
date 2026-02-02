<?php
/**
 * Admin REST API Authentication
 *
 * Checks if REST API endpoints are properly authenticated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0644
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin REST API Authentication
 *
 * @since 1.26033.0644
 */
class Diagnostic_Admin_Rest_Api_Authentication extends Diagnostic_Base {

	protected static $slug = 'admin-rest-api-authentication';
	protected static $title = 'Admin REST API Authentication';
	protected static $description = 'Verifies REST API endpoints require authentication';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check if REST API is exposed
		$rest_enabled = get_option( 'rest_api_enabled', true );
		if ( $rest_enabled ) {
			// Check if REST API requires authentication
			$is_rest_public = apply_filters( 'rest_authentication_errors', null );
			if ( null === $is_rest_public ) {
				$issues[] = __( 'REST API endpoints are publicly accessible without authentication', 'wpshadow' );
			}
		}

		// Check for unprotected custom REST endpoints
		global $wp_rest_server;
		$endpoints = rest_get_endpoints();
		$public_endpoints = 0;

		foreach ( (array) $endpoints as $route => $endpoint ) {
			if ( is_array( $endpoint ) && isset( $endpoint['methods'] ) ) {
				// Check if GET is publicly accessible
				if ( isset( $endpoint['methods']['GET'] ) ) {
					$public_endpoints++;
				}
			}
		}

		if ( $public_endpoints > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of endpoints */
				__( '%d REST endpoints are publicly accessible', 'wpshadow' ),
				$public_endpoints
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 78,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-rest-api-authentication',
			);
		}

		return null;
	}
}
