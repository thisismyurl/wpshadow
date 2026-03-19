<?php
/**
 * Multisite Super Admin Scope Diagnostic
 *
 * In multisite installations, checks that super admin capabilities are
 * appropriately restricted and not granted to regular site administrators.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Super Admin Scope Diagnostic Class
 *
 * Validates super admin privileges in multisite environments.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Multisite_Super_Admin_Scope extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-super-admin-scope';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Super Admin Scope';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates super admin privileges in multisite';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run on multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Get super admins.
		$super_admins = get_super_admins();

		if ( empty( $super_admins ) ) {
			$issues[] = __( 'No super admins found (this is critical)', 'wpshadow' );
		} elseif ( count( $super_admins ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of super admins */
				__( '%d super admins configured (consider limiting to essential personnel only)', 'wpshadow' ),
				count( $super_admins )
			);
		}

		// Check for suspicious super admin accounts.
		$suspicious = array();
		$temp_keywords = array( 'temp', 'test', 'demo', 'trial' );

		foreach ( $super_admins as $username ) {
			$user = get_user_by( 'login', $username );
			if ( ! $user ) {
				$issues[] = sprintf(
					/* translators: %s: username */
					__( 'Super admin "%s" does not exist as a user', 'wpshadow' ),
					$username
				);
				continue;
			}

			// Check for temp keywords.
			foreach ( $temp_keywords as $keyword ) {
				if ( false !== stripos( $username, $keyword ) ) {
					$suspicious[] = $username;
					break;
				}
			}

			// Check if user hasn't logged in recently.
			$last_login = get_user_meta( $user->ID, 'last_login', true );
			if ( empty( $last_login ) || ( time() - strtotime( $last_login ) ) > ( 180 * DAY_IN_SECONDS ) ) {
				$suspicious[] = sprintf(
					/* translators: %s: username */
					__( '%s (inactive for 6+ months)', 'wpshadow' ),
					$username
				);
			}
		}

		if ( ! empty( $suspicious ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of usernames */
				__( 'Suspicious super admin accounts: %s', 'wpshadow' ),
				implode( ', ', array_unique( $suspicious ) )
			);
		}

		// Check if grant_super_admin capability is assigned to non-super-admins.
		$users_with_grant = get_users(
			array(
				'meta_key'   => 'wp_capabilities',
				'meta_value' => 'grant_super_admin',
				'fields'     => array( 'ID', 'user_login' ),
			)
		);

		$non_super_with_grant = array();
		foreach ( $users_with_grant as $user ) {
			if ( ! is_super_admin( $user->ID ) ) {
				$non_super_with_grant[] = $user->user_login;
			}
		}

		if ( ! empty( $non_super_with_grant ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of usernames */
				__( 'Non-super-admins with grant_super_admin capability: %s', 'wpshadow' ),
				implode( ', ', $non_super_with_grant )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of super admin issues */
					__( 'Found %d super admin configuration issues in this multisite network.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'details'      => array(
					'issues'            => $issues,
					'super_admin_count' => count( $super_admins ),
					'recommendation'    => __( 'Review super admin list and remove unnecessary or inactive accounts.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
