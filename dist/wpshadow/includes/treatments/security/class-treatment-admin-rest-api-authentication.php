<?php
/**
 * Admin REST API Authentication Treatment
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
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment: Admin REST API Authentication
 *
 * Uses WordPress REST registry to identify endpoints that appear public.
 * This treatment focuses on exposure risk rather than blocking access.
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
 * **Related Treatments:**
 * - User Enumeration: REST endpoints can leak usernames
 * - Admin Settings Sanitization: Protects REST update inputs
 * - Capability Map Consistency: Ensures auth checks align with roles
 *
 * @since 1.6093.1200
 */
class Treatment_Admin_Rest_Api_Authentication extends Treatment_Base {

	protected static $slug = 'admin-rest-api-authentication';
	protected static $title = 'Admin REST API Authentication';
	protected static $description = 'Verifies REST API endpoints require authentication';
	protected static $family = 'admin-security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Admin_Rest_Api_Authentication' );
	}
}
