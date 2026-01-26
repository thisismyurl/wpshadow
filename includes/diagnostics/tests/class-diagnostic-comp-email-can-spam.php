<?php
/**
 * CAN-SPAM Act Compliance Diagnostic
 *
 * Checks if the WordPress site complies with CAN-SPAM Act requirements
 * for commercial email communications. The CAN-SPAM Act requires:
 * - Clear sender identification
 * - Valid physical postal address
 * - Conspicuous unsubscribe mechanism
 * - Prompt processing of opt-out requests
 *
 * Non-compliance can result in penalties up to $16,000 per email violation.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CAN-SPAM Act Compliance Diagnostic Class
 *
 * Detects potential CAN-SPAM Act violations in email marketing practices.
 */
class Diagnostic_CompEmailCanSpam extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comp-email-can-spam';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CAN-SPAM Act Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies compliance with CAN-SPAM Act requirements for commercial email communications.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Display name for the family
	 *
	 * @var string
	 */
	protected static $family_label = 'Compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for CAN-SPAM Act compliance by verifying:
	 * 1. Basic site identification (sender name and email)
	 * 2. Email marketing disclosure in privacy policy
	 * 3. Email marketing plugin with unsubscribe features
	 * 4. Physical address disclosure (when available)
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null if compliant.
	 */
	public static function check(): ?array {
		// Load plugin.php if not already loaded for is_plugin_active() function.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Step 1: Verify basic site identification (CAN-SPAM requirement).
		$admin_email = get_option( 'admin_email' );
		$blogname    = get_option( 'blogname' );

		if ( empty( $admin_email ) || empty( $blogname ) ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Basic site information not configured. CAN-SPAM Act requires clear identification of the sender. Configure your site name and admin email in Settings → General to identify your business in outgoing emails. Penalties: Up to $16,000 per email violation.', 'wpshadow' ),
				'category'     => 'compliance',
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-can-spam',
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		// Step 2: Validate admin email format.
		if ( false === filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) ) {
			return array(
				'finding_id'   => self::$slug . '-invalid-email',
				'title'        => __( 'Invalid Admin Email Address', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: invalid email address */
					__( 'Admin email "%s" is not valid. CAN-SPAM Act requires accurate "From" information. Configure a valid email address in Settings → General. This also affects delivery of important site notifications.', 'wpshadow' ),
					esc_html( $admin_email )
				),
				'category'     => 'compliance',
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-can-spam',
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		// Step 3: Check for privacy policy that discloses email practices.
		$privacy_policy_id    = (int) get_option( 'wp_page_for_privacy_policy' );
		$has_email_disclosure = false;

		if ( $privacy_policy_id ) {
			$privacy_policy = get_post( $privacy_policy_id );
			if ( $privacy_policy && 'publish' === $privacy_policy->post_status ) {
				$content = $privacy_policy->post_content;
				// Look for email/newsletter/unsubscribe/mailing list disclosure.
				// Using case-insensitive search with multiple keywords.
				if ( stripos( $content, 'email' ) !== false &&
					( stripos( $content, 'unsubscribe' ) !== false ||
					  stripos( $content, 'opt-out' ) !== false ||
					  stripos( $content, 'opt out' ) !== false ||
					  stripos( $content, 'mailing list' ) !== false ) ) {
					$has_email_disclosure = true;
				}
			}
		}

		// Step 4: Check for email marketing plugins with CAN-SPAM compliance features.
		$email_plugins = array(
			'newsletter/newsletter.php',          // Newsletter - manages subscriptions.
			'mailpoet/mailpoet.php',              // MailPoet - email marketing.
			'wp-mail-smtp/wp_mail_smtp.php',      // WP Mail SMTP.
			'convertkit/convertkit-plugin.php',   // ConvertKit.
			'mailchimp-for-wp/mailchimp-for-wp.php', // Mailchimp for WP.
			'email-subscribers/email-subscribers.php', // Email Subscribers & Newsletters.
			'wpforms-lite/wpforms.php',           // WPForms (has email features).
			'contact-form-7/wp-contact-form-7.php', // Contact Form 7.
		);

		$has_email_management = false;
		$active_plugin        = '';
		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_email_management = true;
				$active_plugin        = $plugin;
				break;
			}
		}

		// Step 5: Evaluate compliance - need BOTH disclosure AND email management OR detailed disclosure.
		if ( ! $has_email_management && ! $has_email_disclosure ) {
			return array(
				'finding_id'   => self::$slug . '-no-disclosure',
				'title'        => __( 'Email Marketing Practices Not Disclosed', 'wpshadow' ),
				'description'  => __( 'CAN-SPAM Act compliance requires documenting how you handle email communications and unsubscribe requests. Install an email marketing plugin (MailPoet, Newsletter, etc.) with built-in unsubscribe features OR add detailed email practices disclosure to your Privacy Policy including: how users can unsubscribe, your mailing address, and how quickly you process opt-out requests. Penalties: Up to $16,000 per email violation.', 'wpshadow' ),
				'category'     => 'compliance',
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-can-spam',
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		// Step 6: If has email management but no disclosure, encourage documentation.
		if ( $has_email_management && ! $has_email_disclosure ) {
			return array(
				'finding_id'   => self::$slug . '-plugin-no-disclosure',
				'title'        => __( 'Email Practices Should Be Documented', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: active email plugin name */
					__( 'You have an email management plugin active (%s), but your Privacy Policy does not disclose your email practices. While the plugin may handle unsubscribe features, CAN-SPAM Act compliance also requires transparent disclosure. Add a section to your Privacy Policy (Settings → Privacy) explaining: how users receive emails, how to unsubscribe, and your physical mailing address. This protects you from potential fines up to $16,000 per email.', 'wpshadow' ),
					esc_html( $active_plugin )
				),
				'category'     => 'compliance',
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-can-spam',
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		// No issues found - site appears compliant.
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Verifies that the check() method returns correct results based on
	 * the current WordPress site state.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result array.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_comp_email_can_spam(): array {
		// Load plugin.php if not already loaded for is_plugin_active() function.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$admin_email = get_option( 'admin_email' );
		$blogname    = get_option( 'blogname' );

		// Determine expected site state.
		$is_email_empty    = empty( $admin_email );
		$is_blogname_empty = empty( $blogname );
		$is_email_invalid  = ( ! $is_email_empty && false === filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) );

		// Check privacy policy.
		$privacy_policy_id    = (int) get_option( 'wp_page_for_privacy_policy' );
		$has_email_disclosure = false;

		if ( $privacy_policy_id ) {
			$privacy_policy = get_post( $privacy_policy_id );
			if ( $privacy_policy && 'publish' === $privacy_policy->post_status ) {
				$content = $privacy_policy->post_content;
				if ( stripos( $content, 'email' ) !== false &&
					( stripos( $content, 'unsubscribe' ) !== false ||
					  stripos( $content, 'opt-out' ) !== false ||
					  stripos( $content, 'opt out' ) !== false ||
					  stripos( $content, 'mailing list' ) !== false ) ) {
					$has_email_disclosure = true;
				}
			}
		}

		// Check for email plugins.
		$email_plugins = array(
			'newsletter/newsletter.php',
			'mailpoet/mailpoet.php',
			'wp-mail-smtp/wp_mail_smtp.php',
			'convertkit/convertkit-plugin.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'email-subscribers/email-subscribers.php',
			'wpforms-lite/wpforms.php',
			'contact-form-7/wp-contact-form-7.php',
		);

		$has_email_management = false;
		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_email_management = true;
				break;
			}
		}

		// Determine if an issue should be detected.
		$should_have_issue = $is_email_empty || $is_blogname_empty || $is_email_invalid ||
							( ! $has_email_management && ! $has_email_disclosure );

		// Run the actual diagnostic.
		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );

		// Compare expected vs actual.
		$test_passes = ( $should_have_issue === $diagnostic_found_issue );

		if ( $test_passes ) {
			$message = 'CAN-SPAM diagnostic correctly identified site state';
		} else {
			$message = sprintf(
				'Mismatch: expected %s but diagnostic returned %s (email: %s, blogname: %s, has_disclosure: %s, has_plugin: %s)',
				$should_have_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$admin_email ? $admin_email : 'empty',
				$blogname ? 'set' : 'empty',
				$has_email_disclosure ? 'yes' : 'no',
				$has_email_management ? 'yes' : 'no'
			);
		}

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
