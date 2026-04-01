<?php
/**
 * REST API Media Endpoint Security Treatment
 *
 * Validates media REST API endpoints require proper authentication/authorization.
 * Unprotected media endpoint = attacker uploads/deletes arbitrary media.
 * Could inject malware files or delete site content (DoS).
 *
 * **What This Check Does:**
 * - Tests media endpoint authentication
 * - Validates capability checks (edit_posts required)
 * - Checks if unauthorized users can upload
 * - Confirms deletion requires proper permissions
 * - Tests nonce verification
 * - Returns severity if endpoint unprotected
 *
 * **Why This Matters:**
 * Unprotected media endpoint = arbitrary file control. Scenarios:
 * - /wp-json/wp/v2/media allows unauthenticated uploads
 * - Attacker uploads webshell
 * - Gets code execution
 * - Full site compromise
 *
 * **Business Impact:**
 * Media REST endpoint doesn't check permissions. Attacker uploads webshell
 * disguised as image. Server executes. Attacker has full access. Malware
 * installed. Site compromised. Recovery: $200K+. With auth: attacker can't
 * upload without proper capability. Media endpoint remains safe.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: API endpoints secured
 * - #9 Show Value: Prevents file-based attacks
 * - #10 Beyond Pure: Authorization-first design
 *
 * **Related Checks:**
 * - REST API Authentication Not Enforced (overall API security)
 * - File Permission Security (upload restrictions)
 * - Media API Rate Limiting (media protection)
 *
 * **Learn More:**
 * Media endpoint security: https://wpshadow.com/kb/rest-media-endpoint
 * Video: Securing REST media API (10min): https://wpshadow.com/training/media-api
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Media Endpoint Security Treatment Class
 *
 * Checks if media REST API endpoints have proper authentication
 * and authorization to prevent unauthorized access.
 *
 * **Detection Pattern:**
 * 1. Query /wp-json/wp/v2/media without authentication
 * 2. Test if upload possible without auth
 * 3. Attempt media deletion
 * 4. Check capability verification (edit_posts)
 * 5. Validate nonce requirement
 * 6. Return severity if unprotected
 *
 * **Real-World Scenario:**
 * Media endpoint allows unauthenticated uploads. Attacker discovers. Uploads
 * PHP file. Server executes. Attacker has shell. With auth: endpoint requires
 * authentication token + edit_posts capability. Unauthenticated: 401 error.
 *
 * **Implementation Notes:**
 * - Tests media endpoint access controls
 * - Validates authentication requirement
 * - Checks capability verification
 * - Severity: critical (unauth upload), high (weak auth)
 * - Treatment: require authentication + proper capabilities
 *
 * @since 0.6093.1200
 */
class Treatment_REST_API_Media_Endpoint_Security extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-media-endpoint-security';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Media Endpoint Security';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests authentication and authorization for media REST endpoints';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Tests if REST API media endpoints require proper authentication
	 * and have adequate capability checks.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_REST_API_Media_Endpoint_Security' );
	}
}
