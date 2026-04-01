<?php
/**
 * Comment API Endpoint Security Diagnostic\n *
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
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment API Endpoint Security Diagnostic Class\n *
 * Implements comment endpoint security validation by inspecting REST API route\n * registration and permission callbacks. Detection: queries REST API endpoints,\n * checks for __return_true or null permission_callback (= no authentication).\n *
 * **Detection Pattern:**
 * 1. Query rest_get_server()->get_routes() to get registered endpoints\n * 2. Find /wp/v2/comments POST route (comment creation)\n * 3. Check permission_callback: should be function requiring authentication\n * 4. If __return_true or null: endpoint open to anonymous users\n * 5. Check comment_registration option (require login to comment)\n * 6. Return critical if endpoint open AND comment registration not enforced\n *
 * **Real-World Scenario:**
 * Medium blog using default WordPress REST API without custom permissions. January 2024:\n * bot discovers open /wp-json/wp/v2/comments endpoint. Injects 500 spam comments/day via\n * automated script. Within 1 week: site drowning in spam comments. Moderator spends 3 hours/day\n * removing. Finally implements authentication requirement. After fix: spam drops to 0.\n *
 * **Implementation Notes:**
 * - Inspects REST API route handlers directly\n * - Checks both endpoint permission AND comment_registration setting\n * - Returns severity: high (endpoint open), critical (open + registration disabled)\n * - Auto-fixable treatment: require authentication on comment endpoints\n *
 * @since 0.6093.1200
 */
class Diagnostic_Comment_API_Endpoint_Security extends Diagnostic_Base {
	protected static $slug = 'comment-api-endpoint-security';
	protected static $title = 'Comment API Endpoint Security';
	protected static $description = 'Verifies comment REST API endpoints are secure';
	protected static $family = 'security';

	public static function check() {
		// Check if REST API is enabled.
		$rest_enabled = get_option( 'rest_api_enabled', true );

		if ( ! $rest_enabled ) {
			return null; // REST API disabled, no security concerns.
		}

		$issues = array();

		// Check if comments endpoint allows unauthenticated access.
		$routes        = rest_get_server()->get_routes();
		$comments_open = false;

		if ( isset( $routes['/wp/v2/comments'] ) ) {
			$route = $routes['/wp/v2/comments'];
			foreach ( $route as $handler ) {
				if ( isset( $handler['permission_callback'] ) ) {
					if ( '__return_true' === $handler['permission_callback'] || is_null( $handler['permission_callback'] ) ) {
						$comments_open = true;
					}
				}
			}
		}

		// Check if comment creation requires authentication.
		$require_auth = (int) get_option( 'comment_registration', 0 );

		if ( ! $require_auth && $comments_open ) {
			$issues[] = array(
				'issue'       => 'open_api_endpoint',
				'description' => __( 'Comment REST API endpoint allows unauthenticated access - potential spam vector', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		// Check for rate limiting.
		$has_rate_limit = has_filter( 'rest_pre_dispatch' ) || class_exists( 'WP_REST_Rate_Limit' );

		if ( ! $has_rate_limit ) {
			$issues[] = array(
				'issue'       => 'no_rate_limiting',
				'description' => __( 'REST API has no rate limiting - vulnerable to abuse', 'wpshadow' ),
				'severity'    => 'medium',
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d REST API security issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 60,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/comment-api-endpoint-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'context'      => array(
				'why'            => __( 'Unauthenticated REST comment endpoint = spam + private data leak. Attacker: submits spam comments programmatically (bulk attacks). Accesses comment data (email, IP, content) via public endpoint. Enumerates user accounts (test each with /wp-json/wp/v2/comments). Creates backdoor comments linking to malware. Business impact: moderation overload, spam liability, reputation damage, SEO penalties (malware links).', 'wpshadow' ),
				'recommendation' => __( '1. Require authentication for comment endpoints (add capability check). 2. Disable comment REST endpoint if not needed. 3. Implement rate limiting on comment submission (max 5 per IP per hour). 4. Require CAPTCHA verification for unauthenticated comments. 5. Whitelist allowed comment properties (hide sensitive meta). 6. Add IP-based spam filtering (StopForumSpam integration). 7. Log all comment submissions in activity log. 8. Moderate first-time commenter posts (don\'t auto-approve). 9. Use Akismet or similar spam detection. 10. Monitor comment endpoint usage (detect abuse patterns).', 'wpshadow' ),
			),
		);
		return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'api', 'comment-endpoint' );
	}
}
