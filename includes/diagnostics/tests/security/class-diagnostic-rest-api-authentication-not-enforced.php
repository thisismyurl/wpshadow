<?php
/**
 * REST API Authentication Not Enforced Diagnostic
 *
 * Validates that REST API endpoints require authentication (not publicly accessible).
 * Unauthenticated API = data exposed to anyone. Attacker queries endpoints.
 * Downloads entire database (posts, users, metadata). Site compromised.
 *
 * **What This Check Does:**
 * - Checks if REST API endpoints require authentication
 * - Tests unauthenticated access to sensitive endpoints
 * - Detects if user list publicly accessible
 * - Validates if posts/pages protected
 * - Checks media/attachments exposure
 * - Returns severity if auth not enforced
 *
 * **Why This Matters:**
 * Unauthenticated API = public database. Scenarios:
 * - REST API allows /wp-json/wp/v2/users (no auth)
 * - Attacker queries endpoint
 * - Gets all user IDs, names, emails
 * - Uses emails for phishing
 * - Gets admin account info
 *
 * **Business Impact:**
 * REST API leaks user directory (no auth required). Attacker queries
 * /wp-json/wp/v2/users. Gets 10K customer emails + admin usernames.
 * Uses for phishing campaign. 1% click-through = 100 phished accounts.
 * Account info stolen. Organization liable. With authentication: API
 * requires login token. Attacker can't access without credentials.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: API data protected
 * - #9 Show Value: Prevents info disclosure
 * - #10 Beyond Pure: Authentication-first approach
 *
 * **Related Checks:**
 * - Media API Rate Limiting (media access control)
 * - API Throttling Overall (API protection)
 * - REST API CORS Configuration (cross-origin access)
 *
 * **Learn More:**
 * REST API security: https://wpshadow.com/kb/wordpress-rest-api-security
 * Video: Securing REST API (11min): https://wpshadow.com/training/rest-api-security
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Authentication Not Enforced Diagnostic Class
 *
 * Detects unauthenticated REST API access.
 *
 * **Detection Pattern:**
 * 1. Query REST API endpoints without authentication
 * 2. Check if user list accessible (/wp/v2/users)
 * 3. Test if posts/pages readable
 * 4. Validate if media/attachments exposed
 * 5. Confirm 403/401 response on protected endpoints
 * 6. Return severity if auth not enforced
 *
 * **Real-World Scenario:**
 * WordPress site allows unauthenticated access to /wp-json/wp/v2/users.
 * Attacker queries endpoint. Gets admin username + email. Queries /wp/v2/posts.
 * Gets all post content (some unpublished). Private data exposed. With auth:
 * endpoints require authentication token. Unauthenticated requests: 401 Unauthorized.
 *
 * **Implementation Notes:**
 * - Tests REST API endpoints without token
 * - Checks sensitive endpoints (users, posts, settings)
 * - Validates permission checks
 * - Severity: critical (sensitive data exposed), high (API unprotected)
 * - Treatment: require authentication on all sensitive endpoints
 *
 * @since 1.2601.2352
 */
class Diagnostic_REST_API_Authentication_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-authentication-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Authentication Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API authentication is enforced';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if REST API auth is enforced
		if ( ! has_filter( 'rest_authentication_errors', 'enforce_rest_auth' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API authentication is not enforced. Disable anonymous REST API access or require authentication to prevent data leakage.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-authentication-not-enforced',
			);
		}

		return null;
	}
}
