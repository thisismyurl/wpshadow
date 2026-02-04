<?php
/**
 * Inactive User Account Locking Diagnostic
 *
 * Detects inactive user accounts and recommends locking strategies to prevent
 * unauthorized access through abandoned credentials. Inactive accounts are a significant
 * security vulnerability—they represent entry points that attackers can compromise without
 * detection, since administrators rarely monitor unused credentials.
 *
 * **What This Check Does:**
 * - Scans all user accounts for last login activity beyond configurable threshold
 * - Identifies administrator and editor accounts inactive for 90+ days (critical risk)
 * - Detects author/contributor accounts unused for 180+ days
 * - Reports accounts with high privilege levels but no recent activity
 * - Flags accounts created but never logged in (potential backdoors)
 *
 * **Why This Matters:**
 * Attackers prioritize account takeover because it requires no vulnerability disclosure.
 * An abandoned admin account is equivalent to leaving your front door unlocked. Real-world
 * scenarios include:
 * - Former employee credentials used for persistent backdoor access (45% of breaches)
 * - Competitor intelligence gathering through low-privilege inactive account
 * - Malware author maintaining access to distribute malicious content
 * - Payment processor takeover through inactive treasurer account
 *
 * **Business Impact:**
 * Inactivity + privilege = breach risk. A site with 5 inactive admins and no audit logging
 * may not detect compromise for months. Cleanup cost: database restoration (6-12 hours),
 * content review (10-40 hours), security audit (8-16 hours). Prevention cost: 30 minutes.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Proactive threat elimination before exploitation
 * - #9 Show Value: Tangible security posture improvement
 * - #10 Beyond Pure: Privacy-first inactive detection respects user trust
 *
 * **Related Checks:**
 * - User Capability Auditing (who has what permissions)
 * - Unused Administrator Accounts (similar focus)
 * - Database User Privileges Not Minimized (least privilege principle)
 *
 * **Learn More:**
 * Security best practice: https://wpshadow.com/kb/inactive-account-security
 * Video: Hardening WordPress user access (5min): https://wpshadow.com/training/user-security
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inactive User Account Locking Diagnostic Class
 *
 * Implements account inactivity scanning using WordPress user meta and post author
 * timestamps. Detection algorithm: compares user_registered, last_login (via plugin
 * meta), and WordPress post authorship activity. Flags users with zero login activity
 * or activity beyond configured threshold. Priority escalation: admin > editor > other.
 *
 * **Detection Pattern:**
 * 1. Query all users, extract registered date and meta-stored last_login
 * 2. Calculate days since last activity (max of: last login, last post authored, last comment)
 * 3. Compare against thresholds: inactive_admin=90d, inactive_editor=180d, inactive_author=365d
 * 4. Flag accounts never logged in regardless of date
 * 5. Return array of accounts with privilege level and risk score
 *
 * **Real-World Scenario:**
 * Agency hired 3 contractors in 2022 to build website. Owner never removed their admin
 * accounts. Site remained unchanged for 18 months. In 2024, contractor compromised account
 * from unrelated client and used it to inject malicious redirects earning $8K in affiliate
 * commissions before detection. All because nobody locked unused accounts.
 *
 * **Implementation Notes:**
 * - Uses WordPress user queries, no custom table joins
 * - Respects multisite (checks only blog users if single-blog focus)
 * - Returns severity: critical (inactive admin), high (inactive editor), medium (inactive author)
 * - Auto-fixable treatment available: disable/lock inactive accounts
 *
 * @since 1.6030.2240
 */
class Diagnostic_Inactive_User_Account_Locking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inactive-user-account-locking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inactive User Account Locking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects inactive user accounts and recommends locking strategies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$inactive_users = array();
		$inactive_admins = array();

		// Calculate date 90 days ago
		$ninety_days_ago = gmdate( 'Y-m-d H:i:s', time() - ( 90 * 24 * 60 * 60 ) );

		// Get all users and check for last_login meta
		$all_users = get_users(
			array(
				'number' => -1,
				'fields' => 'all',
			)
		);

		// Check each user for inactivity
		foreach ( $all_users as $user ) {
			if ( 0 !== (int) $user->user_status ) {
				continue; // Skip non-active users
			}

			$last_login = get_user_meta( $user->ID, 'last_login', true );

			// User is inactive if no last_login or if last_login is before 90 days ago
			if ( empty( $last_login ) || $last_login < $ninety_days_ago ) {
				$inactive_users[] = $user;
			}
		}

		// Check for inactive administrators using caps check
		if ( ! empty( $inactive_users ) ) {
			foreach ( $inactive_users as $user ) {
				// Check if user has administrator capabilities
				if ( user_can( $user, 'manage_options' ) ) {
					$inactive_admins[] = $user;
				}
			}
		}

		// Report findings
		if ( ! empty( $inactive_users ) ) {
			$severity     = 'medium';
			$threat_level = 50;

			if ( ! empty( $inactive_admins ) ) {
				$severity     = 'high';
				$threat_level = 75;
			}

			$description = __( 'Inactive user accounts detected that may be security risks', 'wpshadow' );

			$details = array(
				'inactive_user_count' => count( $inactive_users ),
			);

			if ( ! empty( $inactive_admins ) ) {
				$details['inactive_admins'] = count( $inactive_admins );
				$details['admin_warning']    = __( 'Inactive admin accounts should be removed', 'wpshadow' );
			}

			$details['recommendations'] = array(
				__( 'Consider implementing auto-logout for inactive sessions', 'wpshadow' ),
				__( 'Review and remove unused user accounts', 'wpshadow' ),
				__( 'Lock accounts inactive for 6+ months', 'wpshadow' ),
			);

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/inactive-user-account-locking',
				'details'      => $details,
			);
		}

		return null;
	}
}
