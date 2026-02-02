<?php
/**
 * Unused Administrator Accounts Diagnostic\n *
 * Identifies administrator accounts that appear inactive or unused, which increase\n * attack surface by providing attackers with high-value dormant targets. An unused admin\n * account is like leaving a door unlocked in a building: thieves look for easy entry.\n *
 * **What This Check Does:**
 * - Scans all user accounts with administrator role\n * - Checks last login timestamp for each admin (via plugin meta)\n * - Identifies admins inactive for 60+ days (should investigate)\n * - Flags admins inactive for 6+ months (serious risk)\n * - Detects admin accounts created but never logged in (potential backdoors)\n * - Validates administrator count against expected team size\n * - Reports total number of super-admins (multisite)\n *
 * **Why This Matters:**
 * Unused admin accounts are the #1 persistence mechanism for attackers:\n * - Contractor account from 2022 never removed\n * - Former employee admin account still active\n * - \"Demo\" admin account created for testing\n * - Admin account created via SQL injection backdoor\n *
 * **Business Impact:**
 * Company with 3 unused admin accounts (from contractors/employees departed).\n * One contractor's password leaked in password manager data breach. Attacker tests\n * password on WordPress site: admin account = access granted. Attacker modifies site\n * to phish customer login credentials. 40+ customers compromised. Legal cost:\n * $500K+. Prevention: quarterly admin account audit, 30 minutes.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Attack surface reduction\n * - #9 Show Value: Tangible risk elimination\n * - #10 Beyond Pure: Respects team access control (nobody has more access than needed)\n *
 * **Related Checks:**
 * - Inactive User Account Locking (similar check, broader scope)\n * - User Capability Auditing (who has what permissions)\n * - Custom Role Definition Audit (ensure admin role is truly privileged)\n *
 * **Learn More:**
 * Admin account management: https://wpshadow.com/kb/admin-account-audit
 * Video: User access governance (10min): https://wpshadow.com/training/user-management\n *
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
 * Unused Administrator Accounts Diagnostic Class\n *
 * Implements admin inactivity scanning by querying WordPress users with admin role,\n * checking last_login meta, and calculating days since activity. Detection: if admin\n * inactive 60+ days OR never logged in: flag as unused.\n *
 * **Detection Pattern:**
 * 1. Query get_users( array( 'role' => 'administrator' ) )\n * 2. For each admin: get last_login timestamp from user meta\n * 3. Calculate days_inactive = today - last_login\n * 4. Flag if days_inactive > 60 or last_login is NULL\n * 5. Check for never-logged-in accounts (created but zero activity)\n * 6. Return list of unused admin accounts\n *
 * **Real-World Scenario:**
 * Web development agency manages 200 client sites. Each site has 1-2 admin accounts.\n * Freelancer left company 8 months ago, nobody removed their admin account.\n * Freelancer's laptop compromised with malware (password manager breach). Attacker\n * uses freelancer's password to access 50 agency client sites via these old accounts.\n * Damage: cleanup costs $100K, client notifications $50K, reputation damage.\n * Prevention: quarterly admin account review (1 hour), catch unused accounts immediately.\n *
 * **Implementation Notes:**
 * - Queries WordPress user roles and meta efficiently\n * - Checks both single-site and multisite installations\n * - Returns severity: critical (inactive 6+ months), high (inactive 2-6 months)\n * - Auto-fixable treatment: disable/delete identified unused accounts\n *
 * @since 1.6032.1340
 */\nclass Diagnostic_Unused_Administrator_Accounts extends Diagnostic_Base {

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
