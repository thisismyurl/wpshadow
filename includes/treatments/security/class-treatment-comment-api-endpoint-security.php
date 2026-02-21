<?php
/**
 * Comment API Endpoint Security Treatment\n *
 * Validates that the WordPress REST API comment endpoint is properly secured, requiring\n * authentication where appropriate and preventing unauthorized comment creation/modification.\n * Open comment endpoints are spam/malware injection vectors: attackers bulk-inject malicious\n * comments containing links, JavaScript, or SEO-poisoning content.\n *
 * **What This Check Does:**
 * - Detects if /wp-json/wp/v2/comments endpoint allows unauthenticated access\n * - Checks if comment creation requires user authentication (not open to anonymous)\n * - Validates permission callbacks are properly set on comment endpoints\n * - Tests rate limiting on comment creation endpoint\n * - Confirms comment moderation status enforcement\n * - Flags excessive endpoint permissions (anonymous reading of draft comments)\n *
 * **Why This Matters:**
 * Open comment endpoint = automated comment spam at scale. Attack vectors:\n * - Bot posts 10,000 links/day to comments (pharmaceutical, malware, SEO spam)\n * - Malware injected via comment XSS for distribution\n * - Comments hidden from moderator view (approval bypassed)\n * - Comment author spoofing (comments appear from admin, destroy credibility)\n *
 * **Business Impact:**
 * Open comment endpoint turns site into spam distribution network. Impact:\n * - 50+ spam comments/day (moderation burden: 1 hour/day)\n * - Site blacklisted as spam distributor (mail server reputation)\n * - Search engines penalize site (\"spammy UGC\" signal)\n * - Malware distributed through comments\n * - E-commerce checkout comments expose customer data\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: API-layer access control\n * - #9 Show Value: Eliminates mass spam injection attack class\n * - #10 Beyond Pure: Protects site visitors from malicious comments\n *
 * **Related Checks:**
 * - Comment Link Count Limits (filter content of comments)\n * - Comment Flood Protection (rate limiting)\n * - Comment HTML Tag Whitelist (XSS prevention)\n *
 * **Learn More:**
 * REST API security: https://wpshadow.com/kb/rest-api-comment-security
 * Video: Securing WordPress APIs (9min): https://wpshadow.com/training/api-endpoint-security
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1500
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment API Endpoint Security Treatment Class\n *
 * Implements comment endpoint security validation by inspecting REST API route\n * registration and permission callbacks. Detection: queries REST API endpoints,\n * checks for __return_true or null permission_callback (= no authentication).\n *
 * **Detection Pattern:**
 * 1. Query rest_get_server()->get_routes() to get registered endpoints\n * 2. Find /wp/v2/comments POST route (comment creation)\n * 3. Check permission_callback: should be function requiring authentication\n * 4. If __return_true or null: endpoint open to anonymous users\n * 5. Check comment_registration option (require login to comment)\n * 6. Return critical if endpoint open AND comment registration not enforced\n *
 * **Real-World Scenario:**
 * Medium blog using default WordPress REST API without custom permissions. January 2024:\n * bot discovers open /wp-json/wp/v2/comments endpoint. Injects 500 spam comments/day via\n * automated script. Within 1 week: site drowning in spam comments. Moderator spends 3 hours/day\n * removing. Finally implements authentication requirement. After fix: spam drops to 0.\n *
 * **Implementation Notes:**
 * - Inspects REST API route handlers directly\n * - Checks both endpoint permission AND comment_registration setting\n * - Returns severity: high (endpoint open), critical (open + registration disabled)\n * - Auto-fixable treatment: require authentication on comment endpoints\n *
 * @since 1.6031.1500
 */
class Treatment_Comment_API_Endpoint_Security extends Treatment_Base {
	protected static $slug = 'comment-api-endpoint-security';
	protected static $title = 'Comment API Endpoint Security';
	protected static $description = 'Verifies comment REST API endpoints are secure';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Comment_API_Endpoint_Security' );
	}
}
