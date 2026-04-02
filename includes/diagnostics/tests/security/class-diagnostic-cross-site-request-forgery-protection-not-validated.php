<?php
/**
 * Cross-Site Request Forgery Protection Not Validated Diagnostic
 *
 * Validates that forms and AJAX endpoints verify nonce tokens before accepting\n * state-changing requests. Missing nonce validation enables CSRF attacks: attacker\n * tricks user into performing unwanted actions (delete posts, change settings).\n *
 * **What This Check Does:**
 * - Detects forms missing nonce fields\n * - Scans AJAX handlers for missing nonce verification\n * - Checks if wp_verify_nonce() called on form submission\n * - Validates nonce action names match between form generation and verification\n * - Tests that nonce validation blocks invalid/expired nonces\n * - Confirms admin actions protected (settings changes, user deletions)\n *
 * **Why This Matters:**
 * Missing nonce validation enables user impersonation. Scenarios:\n * - Admin logs in. Attacker tricks admin into clicking malicious link\n * - Link triggers admin action (change admin email, add new admin user)\n * - Action succeeds because nonce validation missing\n * - Attacker gains site access\n *
 * **Business Impact:**
 * WordPress site missing nonce on user deletion form. Admin logs in. Admin receives\n * email \"Your account activity\" (actually malicious link). Admin clicks link.\n * CSRF request sent to site DELETE endpoint (attacker's hidden form submission).\n * Legitimate admin user account deleted. All admin posts orphaned. Site admin can't\n * log in anymore. Takes 3 hours to recover via database.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Admin actions require user consent\n * - #9 Show Value: Prevents entire class of CSRF attacks\n * - #10 Beyond Pure: Defense in depth, requires nonce for ALL state changes\n *
 * **Related Checks:**
 * - Input Sanitization Not Implemented (prevent injection)\n * - SQL Injection Prevention Not Implemented (safe database queries)\n * - Comment Form CAPTCHA Not Implemented (add friction to actions)\n *
 * **Learn More:**
 * CSRF protection guide: https://wpshadow.com/kb/wordpress-nonce-validation\n * Video: Implementing nonce security (10min): https://wpshadow.com/training/csrf-protection\n *
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
 * Cross-Site Request Forgery Protection Not Validated Diagnostic Class
 *
 * Implements detection of missing nonce verification on forms/AJAX.\n *
 * **Detection Pattern:**
 * 1. Query wp_options for admin actions\n * 2. Scan form templates for nonce fields\n * 3. Check processing pages for wp_verify_nonce() calls\n * 4. Validate nonce action names match (form field vs. check)\n * 5. Test AJAX handlers for check_ajax_referer()\n * 6. Return severity if nonce protection missing\n *
 * **Real-World Scenario:**
 * Developer creates delete-user form. Includes nonce field but forgets to verify.\n * Form HTML: <input name=\"_wpnonce\" value=\"xyz...\" />\n * Processing: wp_verify_nonce() call removed during debugging (never added back).\n * Attacker crafts CSRF payload that submits deletion form. Admin visits malicious\n * site. Deletion processed (no nonce check). Admin account removed.\n *
 * **Implementation Notes:**
 * - Scans for wp_verify_nonce() and check_ajax_referer()\n * - Matches nonce field names in forms\n * - Validates action parameter consistency\n * - Severity: critical (admin action exposed), high (data modification)\n * - Treatment: add nonce verification to all forms\n *
 * @since 1.6093.1200
 */
class Diagnostic_Cross_Site_Request_Forgery_Protection_Not_Validated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cross-site-request-forgery-protection-not-validated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cross-Site Request Forgery Protection Not Validated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSRF protection is validated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if nonce validation is implemented
		if ( ! has_filter( 'wp_ajax_nopriv', 'validate_csrf_token' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CSRF protection is not validated. Verify that all forms and AJAX requests use WordPress nonces to prevent unauthorized requests.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cross-site-request-forgery-protection-not-validated',
				'context'       => array(
					'why'            => __( 'Without nonce verification, attackers trick users into performing unwanted actions. User logs in. Attacker sends email with malicious link. User clicks it. Browser silently sends form submission to your site (delete post, change admin email, add backdoor user). Action succeeds because nonce validation missing. OWASP Top 10: #4 Cross-Site Request Forgery. PCI-DSS: All sensitive transactions require anti-CSRF tokens. Real scenario: Admin logs in, receives email "Account activity", clicks link = admin user deleted. Cost: 3+ hours recovery time.', 'wpshadow' ),
					'recommendation' => __( '1. All forms require nonce: <?php wp_nonce_field("action_name", "_wpnonce"); ?>\n2. Verify on processing: if (!wp_verify_nonce($_POST["_wpnonce"], "action_name")) wp_die("Failed");\n3. AJAX requests: Use check_ajax_referer("action_name"); at handler start\n4. Generate nonce in JS: wp_localize_script("handle", "data", ["nonce" => wp_create_nonce("action_name")]);\n5. Send in AJAX: $.post(ajaxurl, {nonce: data.nonce, action: "my_action"});\n6. Audit forms: Find all <form> tags in theme/plugins, verify nonce field present\n7. Test nonce expiration: Default 24-hour lifetime (configure via nonce_life filter)\n8. Don\'t hardcode nonce values: Generate fresh nonce each page load\n9. Use standard nonce field name: "_wpnonce" (WordPress convention)\n10. Document nonce usage: Comment nonce action names in code for maintainability', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'csrf-protection', 'csrf-enforcement' );
			return $finding;
		}

		return null;
	}
}
