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
