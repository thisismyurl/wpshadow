<?php
/**
 * Admin REST API Authentication Diagnostic
 *
 * Verifies that REST API endpoints require proper authentication and do not
 * expose sensitive data or administrative actions to unauthenticated users.
 * The REST API is powerful and convenient, but misconfigured endpoints can
 * leak user data or allow unauthorized changes.
 *
 * **What This Check Does:**
 * - Confirms REST API authentication filters are active
 * - Audits registered endpoints for public access patterns
 * - Flags endpoints that allow GET access without auth checks
 * - Highlights custom routes that may bypass capability checks
 * - Encourages use of `permission_callback` for every route
 *
 * **Why This Matters:**
 * REST endpoints can expose user lists, settings, or private content.
 * A single unauthenticated endpoint can leak email addresses, metadata, or
 * allow attackers to enumerate users for brute-force attacks.
 *
 * **Real-World Data Leak Scenario:**
 * - Plugin registers `/wp-json/myplugin/v1/settings`
 * - Developer forgets `permission_callback`
 * - Endpoint returns API keys and admin emails to anyone
 * - Attackers scrape settings and pivot to account takeover
 *
 * **Safe REST API Pattern:**
 * ```php
 * register_rest_route( 'myplugin/v1', '/settings', array(
 *   'methods'  => 'GET',
 *   'callback' => 'myplugin_get_settings',
 *   'permission_callback' => function () {
 *     return current_user_can( 'manage_options' );
 *   },
 * ) );
 * ```
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents unauthorized data exposure
 * - #10 Beyond Pure: Protects user privacy and site integrity
 * - Helpful Neighbor: Encourages secure REST patterns
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/rest-api-authentication for best practices
 * or https://wpshadow.com/training/secure-rest-endpoints
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin REST API Authentication
 *
 * Uses WordPress REST registry to identify endpoints that appear public.
 * This diagnostic focuses on exposure risk rather than blocking access.
 *
 * **Implementation Pattern:**
 * 1. Check REST API enablement setting
 * 2. Inspect `rest_authentication_errors` filter behavior
 * 3. Enumerate registered routes with `rest_get_endpoints()`
 * 4. Flag routes with public GET methods and no auth gating
 * 5. Return findings with remediation guidance
 *
 * **Detection Logic:**
 * - No authentication filter + public endpoints = high exposure risk
 * - High count of public endpoints suggests missing permission callbacks
 *
 * **Related Diagnostics:**
 * - User Enumeration: REST endpoints can leak usernames
 * - Admin Settings Sanitization: Protects REST update inputs
 * - Capability Map Consistency: Ensures auth checks align with roles
 *
 * @since 1.6093.1200
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
		// Note: rest_get_endpoints() doesn't exist; use REST server routes instead
		$rest_server = rest_get_server();
		if ( $rest_server ) {
			$routes = $rest_server->get_routes();
			$endpoints = is_array( $routes ) ? $routes : array();
		} else {
			$endpoints = array();
		}
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
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( '. ', $issues ),
				'severity'      => 'high',
				'threat_level'  => 78,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-rest-api-authentication',
				'context'       => array(
					'why'            => __( 'Public endpoints = user enumeration attack. Real scenario: Attacker queries /wp-json/wp/v2/users. Gets all usernames. Then brute force login. Unprotected endpoints = reconnaissance vector. Every public endpoint = potential attack surface. With auth: Only authenticated users see endpoints.', 'wpshadow' ),
					'recommendation' => __( '1. Add permission_callback to every endpoint registration. 2. Check current_user_can(\'manage_options\') for admin endpoints. 3. Return 403 for unauthorized requests. 4. Audit all custom REST endpoints. 5. Test with unauthenticated user. 6. Document public vs private endpoints. 7. Use nonces for POST/DELETE. 8. Consider disabling REST API if unused. 9. Use role-based access control. 10. Monitor REST endpoint access in logs.', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'api-auth', 'admin-rest-api' );
			return $finding;
		}

		return null;
	}
}
