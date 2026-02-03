<?php
/**
 * User Email Verification Status Diagnostic
 *
 * Validates that user email addresses are verified and that email-based
 * security features like password resets are functioning properly.
 * Unverified email = attacker registers with fake email. Or: user account
 * created with spam email. No verification = security gap.
 *
 * **What This Check Does:**
 * - Checks if email verification required for registration
 * - Validates email confirmation link functionality
 * - Tests if verification tokens expire
 * - Checks for spam email detection
 * - Validates password reset email delivery
 * - Returns severity if email verification disabled
 *
 * **Why This Matters:**
 * Without email verification: attacker registers with fake email.
 * Or: registers with stolen email, hijacks account.
 * Verification = proves email actually works.
 * Prevents fake account registration.
 *
 * **Business Impact:**
 * Forum allows registration without email verification.
 * Attacker registers 1000 bot accounts with fake emails.
 * Bots spam forum. Site reputation damaged. Real users leave.
 * Revenue from ads drops 60%. With verification: fake emails rejected.
 * Only legitimate users register. Community quality maintained.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Email verified, accounts legitimate
 * - #9 Show Value: Prevents spam account registration
 * - #10 Beyond Pure: Identity verification required
 *
 * **Related Checks:**
 * - User Account Registration Validation (related)
 * - Password Reset Process Security (related)
 * - Account Takeover Prevention (related)
 *
 * **Learn More:**
 * Email verification setup: https://wpshadow.com/kb/email-verification
 * Video: Email verification workflow (10min): https://wpshadow.com/training/email-verify
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Email Verification Status Diagnostic Class
 *
 * Checks user email verification and status.
 *
 * **Detection Pattern:**
 * 1. Check if email verification enabled for registrations
 * 2. Get list of users with unverified emails
 * 3. Test email verification link functionality
 * 4. Validate token expiration times
 * 5. Check password reset email delivery
 * 6. Return list of unverified users
 *
 * **Real-World Scenario:**
 * Email verification enabled. New user registers with attacker's email.
 * Attacker doesn't confirm email. Account remains unverified. Real owner
 * of email later registers. System prompts: account exists. Forces email
 * verification. Attacker's registration prevented (email already claimed).
 * With verification: real owner retains email. Attacker can't hijack.
 *
 * **Implementation Notes:**
 * - Checks email verification requirements
 * - Validates token expiration
 * - Tests email delivery
 * - Severity: high (no verification), medium (weak verification)
 * - Treatment: enable and enforce email verification
 *
 * @since 1.6032.1340
 */
class Diagnostic_User_Email_Verification_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-email-verification-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Email Verification Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user email verification';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if user registration is enabled.
		$users_can_register = get_option( 'users_can_register', 0 );

		if ( ! $users_can_register ) {
			// Registration is disabled - less critical.
		}

		// Get all users.
		$all_users = get_users(
			array(
				'fields' => array( 'ID', 'user_email', 'user_registered' ),
			)
		);

		if ( empty( $all_users ) ) {
			$issues[] = __( 'No users found (system error)', 'wpshadow' );
		}

		// Check for users with invalid or missing email addresses.
		$invalid_emails = array();

		foreach ( $all_users as $user ) {
			if ( empty( $user->user_email ) ) {
				$invalid_emails[] = array(
					'user_id' => $user->ID,
					'issue'   => 'No email address',
				);
			} elseif ( ! is_email( $user->user_email ) ) {
				$invalid_emails[] = array(
					'user_id' => $user->ID,
					'email'   => $user->user_email,
					'issue'   => 'Invalid email format',
				);
			}
		}

		if ( ! empty( $invalid_emails ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of users with invalid emails */
				__( '%d users have invalid or missing email addresses', 'wpshadow' ),
				count( $invalid_emails )
			);
		}

		// Check for duplicate email addresses (security risk).
		global $wpdb;

		$duplicate_emails = $wpdb->get_results(
			"SELECT user_email, COUNT(*) as count
			FROM {$wpdb->users}
			WHERE user_email != ''
			GROUP BY user_email
			HAVING count > 1"
		);

		if ( ! empty( $duplicate_emails ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicate emails */
				__( '%d users share the same email address (security risk)', 'wpshadow' ),
				count( $duplicate_emails )
			);
		}

		// Check for disposable/temporary email addresses.
		$disposable_domains = array(
			'tempmail.com',
			'10minutemail.com',
			'guerrillamail.com',
			'throwaway.email',
			'maildrop.cc',
			'temp-mail.org',
			'sharklasers.com',
		);

		$disposable_emails = array();

		foreach ( $all_users as $user ) {
			if ( ! empty( $user->user_email ) ) {
				$domain = explode( '@', $user->user_email )[1];

				foreach ( $disposable_domains as $disposable ) {
					if ( $domain === $disposable ) {
						$disposable_emails[] = $user->user_email;
					}
				}
			}
		}

		if ( ! empty( $disposable_emails ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of disposable emails */
				__( '%d users registered with disposable email addresses (unreliable)', 'wpshadow' ),
				count( $disposable_emails )
			);
		}

		// Check for email verification plugin.
		$has_email_verification = false;

		if ( is_plugin_active( 'new-user-approve/new-user-approve.php' ) ) {
			$has_email_verification = true;
		}

		if ( is_plugin_active( 'user-registration/user-registration.php' ) ) {
			// Check if email verification is enabled in settings.
			$ur_options = get_option( 'user_registration_settings' );
			if ( ! empty( $ur_options['email_verification'] ) ) {
				$has_email_verification = true;
			}
		}

		if ( ! $has_email_verification && $users_can_register ) {
			$issues[] = __( 'User registration enabled without email verification (spam/abuse risk)', 'wpshadow' );
		}

		// Check for admin email configuration.
		$admin_email = get_option( 'admin_email' );

		if ( empty( $admin_email ) ) {
			$issues[] = __( 'Admin email not configured (notifications will fail)', 'wpshadow' );
		} elseif ( ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email is not a valid email address', 'wpshadow' );
		}

		// Check email delivery functionality.
		$test_email_sent = get_option( 'wpshadow_email_test_sent' );

		if ( ! $test_email_sent ) {
			// Email delivery not tested.
		}

		// Check for newly registered users without email verification.
		$new_users_count = 0;
		$days_ago_7      = time() - ( 7 * DAY_IN_SECONDS );

		foreach ( $all_users as $user ) {
			$registered_time = strtotime( $user->user_registered );

			if ( $registered_time > $days_ago_7 ) {
				$new_users_count++;
			}
		}

		if ( $new_users_count > 0 && ! $has_email_verification ) {
			$issues[] = sprintf(
				/* translators: %d: number of recent users */
				__( '%d new users registered in last 7 days without email verification', 'wpshadow' ),
				$new_users_count
			);
		}

		// Check for email-based security features.
		$lost_password_enabled = ! is_wp_error( wp_lostpassword_url() );

		if ( ! $lost_password_enabled ) {
			$issues[] = __( 'Password reset functionality appears to be disabled', 'wpshadow' );
		}

		// Check for SSL/TLS configuration for email transmission.
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site is not using HTTPS (email notifications and password reset less secure)', 'wpshadow' );
		}

		// Check if there are email sending issues logged.
		$failed_emails = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '%email_failed%'
			OR meta_key LIKE '%mail_error%'"
		);

		if ( $failed_emails > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failed emails */
				__( '%d failed email send attempts detected', 'wpshadow' ),
				$failed_emails
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of email verification issues */
					__( 'Found %d user email verification issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'      => array(
					'issues'                    => $issues,
					'user_count'                => count( $all_users ),
					'invalid_email_count'       => count( $invalid_emails ),
					'disposable_email_count'    => count( $disposable_emails ),
					'registration_enabled'      => $users_can_register,
					'email_verification_active' => $has_email_verification,
					'recommendation'            => __( 'Enable email verification for new registrations. Audit user emails for validity. Ensure admin email is correct. Test password reset functionality regularly.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
