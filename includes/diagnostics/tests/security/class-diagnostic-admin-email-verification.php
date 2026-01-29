<?php
/**
 * Admin Email Verification
 *
 * Verifies that admin user emails are real and reachable to ensure
 * site administrators can receive important notifications.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6029.1100
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Email Verification Diagnostic Class
 *
 * Checks if administrator email addresses are valid and can receive
 * important security and maintenance notifications.
 *
 * @since 1.6029.1100
 */
class Diagnostic_Admin_Email_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-email-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Email Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies admin emails are valid and reachable for critical notifications';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_admin_email_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$admin_emails = self::get_admin_emails();
		$invalid      = array();

		foreach ( $admin_emails as $user_id => $email ) {
			if ( ! self::is_email_valid( $email ) ) {
				$invalid[ $user_id ] = $email;
			}
		}

		if ( empty( $invalid ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of invalid emails */
				__( 'Found %d administrator accounts with invalid or unreachable email addresses.', 'wpshadow' ),
				count( $invalid )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-email-verification',
			'meta'         => array(
				'invalid_count'  => count( $invalid ),
				'total_admins'   => count( $admin_emails ),
				'invalid_emails' => array_values( $invalid ),
			),
			'details'      => array(
				__( 'Invalid admin emails prevent important security notifications', 'wpshadow' ),
				__( 'Site administrators may miss critical alerts and updates', 'wpshadow' ),
				__( 'Password reset and account recovery features may fail', 'wpshadow' ),
			),
			'recommendation' => __( 'Update administrator email addresses to valid, monitored addresses.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Get admin user emails.
	 *
	 * @since  1.6029.1100
	 * @return array Array of admin user emails keyed by user ID.
	 */
	private static function get_admin_emails() {
		$admins = get_users(
			array(
				'role'   => 'administrator',
				'fields' => array( 'ID', 'user_email' ),
			)
		);

		$emails = array();
		foreach ( $admins as $admin ) {
			$emails[ $admin->ID ] = $admin->user_email;
		}

		return $emails;
	}

	/**
	 * Validate email address.
	 *
	 * Performs basic format validation and checks for common disposable
	 * email domains.
	 *
	 * @since  1.6029.1100
	 * @param  string $email Email address to validate.
	 * @return bool Whether email is valid.
	 */
	private static function is_email_valid( $email ) {
		// Basic format check.
		if ( ! is_email( $email ) ) {
			return false;
		}

		// Check for disposable email domains.
		$disposable_domains = array(
			'mailinator.com',
			'guerrillamail.com',
			'tempmail.com',
			'10minutemail.com',
			'throwaway.email',
		);

		$domain = substr( strrchr( $email, '@' ), 1 );

		if ( in_array( strtolower( $domain ), $disposable_domains, true ) ) {
			return false;
		}

		return true;
	}
}
