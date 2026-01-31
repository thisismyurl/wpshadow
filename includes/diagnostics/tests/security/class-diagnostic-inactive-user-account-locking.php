<?php
/**
 * Inactive User Account Locking Diagnostic
 *
 * Detects inactive user accounts and recommends locking strategies.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inactive User Account Locking Diagnostic
 *
 * Identifies inactive user accounts that should be monitored or locked.
 *
 * @since 1.2601.2240
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
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();
		$inactive_users = array();

		// Calculate date 90 days ago
		$ninety_days_ago = gmdate( 'Y-m-d H:i:s', time() - ( 90 * 24 * 60 * 60 ) );

		// Get users not logged in for 90+ days
		$inactive = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID, u.user_login, MAX(pm.meta_value) as last_login
				FROM {$wpdb->users} u
				LEFT JOIN {$wpdb->usermeta} pm ON (u.ID = pm.user_id AND pm.meta_key = 'last_login')
				WHERE u.user_status = 0
				AND (pm.meta_value IS NULL OR pm.meta_value < %s)
				GROUP BY u.ID
				LIMIT 100",
				$ninety_days_ago
			)
		);

		if ( $inactive && is_array( $inactive ) ) {
			$inactive_users = $inactive;
		}

		// Check for unused administrator accounts
		$admin_users = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID, u.user_login, u.user_registered
				FROM {$wpdb->users} u
				INNER JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id)
				WHERE um.meta_key = %s
				AND um.meta_value LIKE %s",
				$wpdb->prefix . 'capabilities',
				'%administrator%'
			)
		);

		$inactive_admins = array();
		if ( $admin_users && is_array( $admin_users ) ) {
			foreach ( $admin_users as $admin ) {
				foreach ( $inactive_users as $inactive ) {
					if ( $admin->ID === $inactive->ID ) {
						$inactive_admins[] = $admin;
						break;
					}
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
