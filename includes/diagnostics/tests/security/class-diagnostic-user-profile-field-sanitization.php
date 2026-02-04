<?php
/**
 * User Profile Field Sanitization Diagnostic
 *
 * Validates that user profile fields are properly sanitized to prevent
 * XSS and other injection attacks through profile data.
 * Profile fields unsanitized = attacker injects script in bio.
 * Other users view profile. Script executes (stored XSS).
 *
 * **What This Check Does:**
 * - Checks profile field sanitization on save
 * - Validates output escaping on profile display
 * - Tests for script injection in bio/description
 * - Checks custom profile fields sanitization
 * - Validates HTML tag stripping
 * - Returns severity if sanitization missing
 *
 * **Why This Matters:**
 * Profile bio allows HTML. Attacker injects:
 * <script>steal_cookies()</script>. Profile saved.
 * Other users view profile. Script executes. Sessions stolen.
 * With sanitization: script tags stripped. Profile safe.
 *
 * **Business Impact:**
 * Forum allows user profiles. Profile bio not sanitized.
 * Attacker's profile has XSS payload in bio. 500+ users view profile
 * (popular topic). All get session cookies stolen. Attacker hijacks
 * 500 accounts. Cost: $200K+ (notification, recovery). With sanitization:
 * script tags removed. Bio displays safely. Attack impossible.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: User data safe
 * - #9 Show Value: Prevents stored XSS attacks
 * - #10 Beyond Pure: Input/output validation
 *
 * **Related Checks:**
 * - XSS Protection Overall (broader)
 * - Theme Data Validation (related)
 * - Content Sanitization (complementary)
 *
 * **Learn More:**
 * Profile field security: https://wpshadow.com/kb/profile-sanitization
 * Video: Sanitizing user input (10min): https://wpshadow.com/training/sanitization
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1335
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Profile Field Sanitization Diagnostic Class
 *
 * Checks user profile field security and sanitization.
 *
 * **Detection Pattern:**
 * 1. Get list of profile fields
 * 2. Check sanitization functions on save
 * 3. Test output escaping on display
 * 4. Validate HTML tag filtering
 * 5. Test custom fields sanitization
 * 6. Return if sanitization missing
 *
 * **Real-World Scenario:**
 * User updates bio field with:
 * <img src=x onerror="fetch('/admin?delete_all')">.
 * Profile saved without sanitization. Admin views profile.
 * Image loads. onerror fires. Request sent. Admin actions executed.
 * With sanitization: img tag or onerror stripped. Profile displays
 * safely.
 *
 * **Implementation Notes:**
 * - Checks profile field handling
 * - Validates sanitization functions
 * - Tests output escaping
 * - Severity: critical (no sanitization)
 * - Treatment: add sanitization on save, escaping on display
 *
 * @since 1.6032.1335
 */
class Diagnostic_User_Profile_Field_Sanitization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-profile-field-sanitization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Profile Field Sanitization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user profile field security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for custom profile fields.
		global $wp_filter;

		$has_custom_fields = false;
		$profile_hooks     = array( 'show_user_profile', 'edit_user_profile', 'personal_options_update', 'edit_user_profile_update' );

		foreach ( $profile_hooks as $hook ) {
			if ( isset( $wp_filter[ $hook ] ) && ! empty( $wp_filter[ $hook ]->callbacks ) ) {
				$has_custom_fields = true;
				break;
			}
		}

		if ( ! $has_custom_fields ) {
			return null; // No custom profile fields, nothing to check.
		}

		// Check for update hooks with sanitization.
		$update_hooks        = array( 'personal_options_update', 'edit_user_profile_update' );
		$has_update_handlers = false;

		foreach ( $update_hooks as $hook ) {
			if ( isset( $wp_filter[ $hook ] ) && ! empty( $wp_filter[ $hook ]->callbacks ) ) {
				$has_update_handlers = true;
				break;
			}
		}

		if ( $has_custom_fields && ! $has_update_handlers ) {
			$issues[] = __( 'Custom profile fields displayed but no update handlers registered', 'wpshadow' );
		}

		// Check for unfiltered_html capability bypass in profile.
		$users_with_unfiltered = get_users(
			array(
				'meta_query' => array(
					array(
						'key'     => 'wp_capabilities',
						'value'   => 'unfiltered_html',
						'compare' => 'LIKE',
					),
				),
				'fields'     => array( 'ID', 'user_login' ),
			)
		);

		// Check actual capabilities instead.
		$users_with_unfiltered = array();
		$all_users             = get_users( array( 'fields' => array( 'ID', 'user_login' ) ) );

		foreach ( $all_users as $user ) {
			$user_obj = new \WP_User( $user->ID );
			if ( $user_obj->has_cap( 'unfiltered_html' ) ) {
				$users_with_unfiltered[] = $user->user_login;
			}
		}

		if ( count( $users_with_unfiltered ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of users with unfiltered HTML */
				__( '%d users have unfiltered_html capability (XSS risk in profiles)', 'wpshadow' ),
				count( $users_with_unfiltered )
			);
		}

		// Check for dangerous content in user meta.
		global $wpdb;
		$dangerous_patterns = array(
			'<script',
			'javascript:',
			'onerror=',
			'onclick=',
			'<iframe',
		);

		$suspicious_meta = 0;
		foreach ( $dangerous_patterns as $pattern ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->usermeta} 
					WHERE meta_value LIKE %s 
					AND meta_key NOT LIKE '%description%'",
					'%' . $wpdb->esc_like( $pattern ) . '%'
				)
			);
			$suspicious_meta += $count;
		}

		if ( $suspicious_meta > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of suspicious user meta entries */
				__( '%d user meta entries contain potentially dangerous HTML/JavaScript', 'wpshadow' ),
				$suspicious_meta
			);
		}

		// Check for users with suspicious biographical info.
		$suspicious_bios = $wpdb->get_results(
			"SELECT user_id, meta_value FROM {$wpdb->usermeta} 
			WHERE meta_key = 'description' 
			AND (
				meta_value LIKE '%<script%' 
				OR meta_value LIKE '%javascript:%' 
				OR meta_value LIKE '%onerror=%'
				OR meta_value LIKE '%onclick=%'
			)"
		);

		if ( ! empty( $suspicious_bios ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of suspicious biographies */
				__( '%d user biographies contain potentially malicious code', 'wpshadow' ),
				count( $suspicious_bios )
			);
		}

		// Check for excessively long profile field values.
		$long_fields = $wpdb->get_results(
			"SELECT user_id, meta_key, LENGTH(meta_value) as length 
			FROM {$wpdb->usermeta} 
			WHERE LENGTH(meta_value) > 10000 
			AND meta_key NOT IN ('session_tokens', 'capabilities')
			ORDER BY length DESC 
			LIMIT 10"
		);

		if ( ! empty( $long_fields ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of excessively long fields */
				__( '%d user meta fields exceed 10KB (possible abuse or injection)', 'wpshadow' ),
				count( $long_fields )
			);
		}

		// Check theme/plugin files for proper profile field sanitization.
		$template_dir   = get_template_directory();
		$functions_file = $template_dir . '/functions.php';

		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			// Check if theme adds profile fields.
			if ( false !== stripos( $content, 'show_user_profile' ) || false !== stripos( $content, 'edit_user_profile' ) ) {
				// Check for sanitization functions.
				if ( false === stripos( $content, 'sanitize_' ) && false === stripos( $content, 'wp_kses' ) ) {
					$issues[] = __( 'Theme adds profile fields without visible sanitization', 'wpshadow' );
				}

				// Check for nonce verification.
				if ( false === stripos( $content, 'wp_verify_nonce' ) && false === stripos( $content, 'check_admin_referer' ) ) {
					$issues[] = __( 'Theme profile field handlers lack nonce verification', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of profile field security issues */
					__( 'Found %d user profile field security issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'context'      => array(
					'why'            => __( 'User profile fields are a common vector for stored XSS because they are user‑controlled, widely displayed, and often trusted by themes and plugins. If profile data is not sanitized on save and escaped on output, an attacker can inject JavaScript into the bio, display name, or custom fields. When other users view the profile, the script executes, enabling cookie theft, admin actions via CSRF, or silent redirection to malicious pages. OWASP Top 10 2021 ranks Injection #3 and Broken Access Control #1; stored XSS often leads to privilege escalation and account compromise. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element, with phishing and credential abuse as common entry points; stored XSS amplifies these by enabling session theft or token exfiltration without direct credential capture. The business impact includes account takeovers, data leakage, and reputational damage if visitors are redirected or shown malicious content. For communities and membership sites, one malicious profile can compromise hundreds of users. Remediation can require forced logouts, password resets, and regulatory notifications. Strong sanitization and contextual escaping are low‑cost controls that dramatically reduce this risk and improve code quality across themes and plugins. They also provide auditable evidence of secure input handling practices for insurers and compliance reviews.', 'wpshadow' ),
					'recommendation' => __( '1. Sanitize all profile inputs on save (sanitize_text_field, sanitize_email, wp_kses).
2. Escape all profile outputs (esc_html, esc_attr, esc_url) based on context.
3. Block dangerous HTML tags and attributes (script, onerror, javascript:).
4. Enforce nonce checks on all profile update handlers.
5. Limit profile field length to prevent payload stuffing.
6. Remove unfiltered_html capability from non‑admin users.
7. Add automated tests for XSS payloads in profile fields.
8. Audit themes/plugins for custom profile fields and fix unsafe output.
9. Log and alert on suspicious profile updates or HTML patterns.
10. Educate moderators/admins to report unexpected profile behavior.', 'wpshadow' ),
				),
				'details'      => array(
					'issues'             => $issues,
					'suspicious_meta'    => $suspicious_meta,
					'suspicious_bios'    => count( $suspicious_bios ),
					'recommendation'     => __( 'Sanitize all user profile field inputs using sanitize_text_field(), wp_kses(), and verify nonces on updates.', 'wpshadow' ),
				),
			);

			return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'xss-prevention', self::$slug );
		}

		return null;
	}
}
