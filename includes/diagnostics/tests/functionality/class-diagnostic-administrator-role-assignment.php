<?php
/**
 * Administrator Role Assignment Diagnostic
 *
 * Validates administrator role assignments to ensure proper access control
 * and detect potentially unnecessary or risky admin accounts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Administrator Role Assignment Diagnostic Class
 *
 * Checks administrator role assignments and security.
 *
 * @since 1.6032.1330
 */
class Diagnostic_Administrator_Role_Assignment extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'administrator-role-assignment';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Administrator Role Assignment';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates administrator role assignments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get all administrators.
		$admins = get_users(
			array(
				'role'   => 'administrator',
				'fields' => array( 'ID', 'user_login', 'user_email', 'user_registered' ),
			)
		);

		if ( empty( $admins ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Critical: No administrator accounts found!', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 100,
				'auto_fixable' => false,
				'details'      => array(
					'recommendation' => __( 'Create an administrator account immediately to maintain site access.', 'wpshadow' ),
				),
			);
		}

		// Check for excessive administrators.
		if ( count( $admins ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of administrators */
				__( '%d administrator accounts (consider limiting to essential personnel)', 'wpshadow' ),
				count( $admins )
			);
		}

		// Check for suspicious admin accounts.
		$suspicious_admins = array();
		$temp_keywords     = array( 'temp', 'test', 'demo', 'trial', 'staging' );

		foreach ( $admins as $admin ) {
			$flags = array();

			// Check username for temp keywords.
			foreach ( $temp_keywords as $keyword ) {
				if ( false !== stripos( $admin->user_login, $keyword ) ) {
					$flags[] = sprintf(
						/* translators: %s: keyword found */
						__( 'Username contains "%s"', 'wpshadow' ),
						$keyword
					);
					break;
				}
			}

			// Check for generic admin usernames.
			if ( in_array( strtolower( $admin->user_login ), array( 'admin', 'administrator', 'root', 'test' ), true ) ) {
				$flags[] = __( 'Generic/insecure username', 'wpshadow' );
			}

			// Check email domain.
			if ( preg_match( '/@(tempmail|guerrillamail|10minutemail|throwaway)/i', $admin->user_email ) ) {
				$flags[] = __( 'Disposable email address', 'wpshadow' );
			}

			// Check for recent creation.
			$days_old = ( time() - strtotime( $admin->user_registered ) ) / DAY_IN_SECONDS;
			if ( $days_old < 7 ) {
				$flags[] = sprintf(
					/* translators: %d: days since creation */
					__( 'Created %d days ago', 'wpshadow' ),
					absint( $days_old )
				);
			}

			if ( ! empty( $flags ) ) {
				$suspicious_admins[] = array(
					'user_id'    => $admin->ID,
					'user_login' => $admin->user_login,
					'user_email' => $admin->user_email,
					'flags'      => $flags,
				);
			}
		}

		if ( ! empty( $suspicious_admins ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of suspicious admins */
				__( '%d administrator accounts have suspicious characteristics', 'wpshadow' ),
				count( $suspicious_admins )
			);
		}

		// Check for inactive administrators.
		$inactive_admins = array();
		foreach ( $admins as $admin ) {
			$last_login = get_user_meta( $admin->ID, 'last_login', true );

			if ( empty( $last_login ) ) {
				// Check last post edit as fallback.
				global $wpdb;
				$last_activity = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT MAX(post_modified) FROM {$wpdb->posts} WHERE post_author = %d",
						$admin->ID
					)
				);

				if ( $last_activity ) {
					$days_inactive = ( time() - strtotime( $last_activity ) ) / DAY_IN_SECONDS;
				} else {
					$days_inactive = ( time() - strtotime( $admin->user_registered ) ) / DAY_IN_SECONDS;
				}

				if ( $days_inactive > 180 ) {
					$inactive_admins[] = array(
						'user_login'     => $admin->user_login,
						'days_inactive'  => absint( $days_inactive ),
					);
				}
			}
		}

		if ( ! empty( $inactive_admins ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of inactive admins */
				__( '%d administrators inactive for 180+ days (security review recommended)', 'wpshadow' ),
				count( $inactive_admins )
			);
		}

		// Check for admins without 2FA (if plugin installed).
		if ( is_plugin_active( 'two-factor/two-factor.php' ) || is_plugin_active( 'wordfence/wordfence.php' ) ) {
			// Check which admins have 2FA enabled.
			$admins_without_2fa = array();

			foreach ( $admins as $admin ) {
				$has_2fa = get_user_meta( $admin->ID, '_two_factor_enabled', true );
				if ( ! $has_2fa ) {
					$admins_without_2fa[] = $admin->user_login;
				}
			}

			if ( count( $admins_without_2fa ) > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of admins without 2FA */
					__( '%d administrators do not have 2FA enabled', 'wpshadow' ),
					count( $admins_without_2fa )
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of admin assignment issues */
					__( 'Found %d administrator role assignment concerns.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'issues'            => $issues,
					'admin_count'       => count( $admins ),
					'suspicious_admins' => $suspicious_admins,
					'inactive_admins'   => array_slice( $inactive_admins, 0, 10 ),
					'recommendation'    => __( 'Limit administrators to essential personnel, remove inactive accounts, and enable 2FA for all admins.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
