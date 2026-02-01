<?php
/**
 * Unused Administrator Accounts Diagnostic
 *
 * Identifies administrator accounts that appear inactive or unused,
 * which can increase attack surface and security risk.
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
 * Unused Administrator Accounts Diagnostic Class
 *
 * Checks for inactive or unused administrator accounts.
 *
 * @since 1.6032.1340
 */
class Diagnostic_Unused_Administrator_Accounts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unused-administrator-accounts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unused Administrator Accounts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies inactive administrator accounts that may pose a risk';

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

		$admins = get_users(
			array(
				'role'   => 'administrator',
				'fields' => array( 'ID', 'user_login', 'user_email', 'user_registered' ),
			)
		);

		if ( empty( $admins ) ) {
			$issues[] = __( 'No administrator accounts found (system error)', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No administrator accounts found.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 100,
				'auto_fixable' => false,
				'details'      => array(
					'recommendation' => __( 'Restore at least one administrator account immediately.', 'wpshadow' ),
				),
			);
		}

		// Define inactivity threshold (days).
		$inactivity_days = 180;
		$inactive_admins = array();
		$never_logged_in = array();

		foreach ( $admins as $admin ) {
			$last_login = get_user_meta( $admin->ID, 'wpshadow_last_login', true );
			$last_seen  = get_user_meta( $admin->ID, 'last_login', true );

			$last_activity = 0;
			if ( ! empty( $last_login ) ) {
				$last_activity = absint( $last_login );
			} elseif ( ! empty( $last_seen ) ) {
				$last_activity = absint( $last_seen );
			}

			if ( 0 === $last_activity ) {
				$never_logged_in[] = $admin->user_login;
				continue;
			}

			$days_inactive = ( time() - $last_activity ) / DAY_IN_SECONDS;
			if ( $days_inactive >= $inactivity_days ) {
				$inactive_admins[] = $admin->user_login;
			}
		}

		if ( ! empty( $never_logged_in ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of admins */
				__( '%d administrator(s) have never logged in: %s', 'wpshadow' ),
				count( $never_logged_in ),
				implode( ', ', $never_logged_in )
			);
		}

		if ( ! empty( $inactive_admins ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of admins */
				__( '%d administrator(s) inactive for %d+ days: %s', 'wpshadow' ),
				count( $inactive_admins ),
				$inactivity_days,
				implode( ', ', $inactive_admins )
			);
		}

		// Check for admin accounts with weak usernames.
		$weak_usernames = array( 'admin', 'administrator', 'root', 'test', 'demo' );
		$suspicious     = array();

		foreach ( $admins as $admin ) {
			if ( in_array( strtolower( $admin->user_login ), $weak_usernames, true ) ) {
				$suspicious[] = $admin->user_login;
			}
		}

		if ( ! empty( $suspicious ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of admins */
				__( '%d administrator(s) have weak usernames: %s', 'wpshadow' ),
				count( $suspicious ),
				implode( ', ', $suspicious )
			);
		}

		// Check if admin emails are valid.
		$invalid_emails = array();

		foreach ( $admins as $admin ) {
			if ( empty( $admin->user_email ) || ! is_email( $admin->user_email ) ) {
				$invalid_emails[] = $admin->user_login;
			}
		}

		if ( ! empty( $invalid_emails ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of admins */
				__( '%d administrator(s) have invalid email addresses: %s', 'wpshadow' ),
				count( $invalid_emails ),
				implode( ', ', $invalid_emails )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of admin account issues */
					__( 'Found %d administrator account hygiene issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'details'      => array(
					'issues'          => $issues,
					'admin_count'     => count( $admins ),
					'inactive_count'  => count( $inactive_admins ),
					'never_logged_in' => count( $never_logged_in ),
					'recommendation'  => __( 'Remove unused admin accounts, enforce strong usernames, and ensure admins log in regularly. Use 2FA for all admins.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
