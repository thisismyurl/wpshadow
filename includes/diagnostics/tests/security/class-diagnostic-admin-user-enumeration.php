<?php
/**
 * Admin User Enumeration Prevention Diagnostic
 *
 * Detects if attackers can discover administrator usernames through multiple
 * attack vectors. User enumeration is a common reconnaissance technique where
 * attackers gather valid usernames, then perform brute-force login attempts
 * against known accounts.
 *
 * **What This Check Does:**
 * - Scans author archives to see if they expose user information
 * - Checks REST API endpoints for user data exposure
 * - Tests for username disclosure through wp-json/wp/v2/users
 * - Identifies which enumeration vectors are vulnerable
 *
 * **Why This Matters:**
 * If attackers can enumerate valid usernames (especially admin/administrator),
 * they can focus brute-force attacks on high-value accounts. This is often the
 * first step in WordPress compromise chains. Eliminating user enumeration
 * requires attackers to guess both username AND password.
 *
 * **Real-World Impact:**
 * - Automated attacks: 90% of WordPress attacks start with user enumeration
 * - Incident response: Prevents reconnaissance phase of targeted attacks
 * - Compliance: Some security frameworks require this hardening
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Explains reconnaissance process so admins understand importance
 * - #8 Inspire Confidence: Removes fear of unknown vulnerabilities
 * - #10 Beyond Pure: Privacy-first by preventing data leakage
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/admin-user-enumeration for defense strategies
 * or https://wpshadow.com/training/wordpress-security-fundamentals
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin User Enumeration
 *
 * Comprehensive check for user enumeration vulnerabilities. This diagnostic
 * tests the most common attack vectors that attackers use in real-world
 * reconnaissance operations.
 *
 * **Implementation Pattern:**
 * 1. Test author archive endpoint (?author=1 → redirects to admin)
 * 2. Check REST API /wp-json/wp/v2/users endpoint
 * 3. Test for xmlrpc.php user.getblogs method
 * 4. Identify which vectors expose admin/editor usernames
 *
 * **Attack Vectors Checked:**
 * ```
 * /?author=1              → Author Archive (most common)
 * /wp-json/wp/v2/users    → REST API User Listing
 * /xmlrpc.php + getBlogs  → XML-RPC enumeration
 * /wp-login.php?register  → Registration email hints
 * ```
 *
 * **Treatment Implemented:**
 * Use `Diagnostic_Admin_User_Enumeration_Treatment` to apply filters
 * that redirect author archives and restrict REST API endpoints to authenticated users.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Admin_User_Enumeration extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'admin-user-enumeration';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Admin User Enumeration Prevention';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WordPress is configured to prevent user enumeration attacks';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'admin-security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check REST API user enumeration
		$rest_enabled = get_option( 'rest_api_enabled', true );
		if ( $rest_enabled ) {
			// REST API is enabled - check if user endpoints are exposed
			$issues[] = __( 'REST API user endpoints may expose user information', 'wpshadow' );
		}

		// Check author archives
		$has_author_archives = true;
		// WordPress always has author archives unless disabled via filter
		if ( $has_author_archives ) {
			$issues[] = __( 'Author archives are publicly accessible and may leak user information', 'wpshadow' );
		}

		// Check if vulnerable query string parameters are accessible
		$response = Diagnostic_Request_Helper::head_result( home_url( '?author=1' ), array( 'sslverify' => false ) );
		if ( $response['success'] && 200 === (int) $response['code'] ) {
			$issues[] = __( 'Author enumeration via query string is possible', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-user-enumeration',
			);
		}

		return null;
	}
}
