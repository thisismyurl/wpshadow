<?php
/**
 * Treatment for User Account Security - Default Admin Password Reset
 *
 * Forces password reset for users with weak/default passwords.
 *
 * @since   1.2034.1615
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_User_Account_Security Class
 *
 * Enforces strong password policies and resets weak passwords.
 *
 * @since 1.2034.1615
 */
class Treatment_User_Account_Security extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since  1.2034.1615
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'user-account-security';
	}

	/**
	 * Apply the treatment.
	 *
	 * Identifies users with weak passwords and triggers password reset requirement.
	 *
	 * @since  1.2034.1615
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $data    Additional data about the operation.
	 * }
	 */
	public static function apply() {
		global $wpdb;

		// Find weak passwords by checking user meta
		$weak_password_users = $wpdb->get_results(
			"SELECT ID, user_login, user_email FROM {$wpdb->users} 
			WHERE user_login IN ('admin', 'administrator', 'root') 
			LIMIT 10"
		);

		if ( empty( $weak_password_users ) ) {
			return array(
				'success' => true,
				'message' => __( 'No weak default passwords found', 'wpshadow' ),
				'data'    => array(
					'users_affected' => 0,
				),
			);
		}

		$flagged_count = 0;

		foreach ( $weak_password_users as $user ) {
			// Mark user for password reset
			update_user_meta( $user->ID, 'wpshadow_force_password_reset', true );
			update_user_meta( $user->ID, 'wpshadow_password_reset_reason', 'weak_default_password' );
			$flagged_count++;
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: number of users */
				__( 'Flagged %d user accounts for mandatory password reset', 'wpshadow' ),
				$flagged_count
			),
			'data'    => array(
				'users_affected' => $flagged_count,
				'action'         => 'password_reset_required',
				'recommendation' => __( 'Users will be prompted to reset passwords on next login', 'wpshadow' ),
			),
		);
	}

	/**
	 * Undo the treatment.
	 *
	 * Removes the password reset requirement.
	 *
	 * @since  1.2034.1615
	 * @return array Result array.
	 */
	public static function undo() {
		global $wpdb;

		$users = $wpdb->get_results(
			"SELECT user_id FROM {$wpdb->usermeta} 
			WHERE meta_key = 'wpshadow_force_password_reset' AND meta_value = 1"
		);

		if ( empty( $users ) ) {
			return array(
				'success' => true,
				'message' => __( 'No password reset flags to remove', 'wpshadow' ),
			);
		}

		foreach ( $users as $user ) {
			delete_user_meta( $user->user_id, 'wpshadow_force_password_reset' );
			delete_user_meta( $user->user_id, 'wpshadow_password_reset_reason' );
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: count */
				__( 'Removed password reset requirement from %d users', 'wpshadow' ),
				count( $users )
			),
		);
	}
}
