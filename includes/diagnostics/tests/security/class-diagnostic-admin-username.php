<?php
/**
 * Diagnostic: Admin Username Detection
 *
 * Checks if a user with username 'admin' exists.
 * This is a major security risk as attackers always try 'admin' first in brute force attacks.
 *
 * Philosophy: Helpful neighbor warns about danger (#1), educates why it matters (#6)
 * KB Link: https://wpshadow.com/kb/security-admin-username
 * Training: https://wpshadow.com/training/security-admin-username
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Username Diagnostic Class
 *
 * Detects the default 'admin' username which is a primary brute force target.
 */
class Diagnostic_Admin_Username extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-username';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Default "admin" Username Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'A user account with username "admin" exists, making your site a target for brute force attacks.';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Check if 'admin' user exists
		$admin_user = get_user_by( 'login', 'admin' );

		if ( ! $admin_user ) {
			return null; // No admin user, all good
		}

		// Check if it's an administrator
		$is_admin     = in_array( 'administrator', (array) $admin_user->roles, true );
		$threat_level = $is_admin ? 75 : 60;

		// Check for additional risk factors
		$risk_factors = array();

		// Check when last login was (if tracking is available)
		$last_login = get_user_meta( $admin_user->ID, 'last_login', true );
		if ( $last_login && ( time() - $last_login ) < ( 30 * DAY_IN_SECONDS ) ) {
			$risk_factors[] = __( 'Account is actively used', 'wpshadow' );
			$threat_level  += 10;
		}

		// Check if it's the only admin
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		if ( count( $admin_users ) === 1 ) {
			$risk_factors[] = __( 'This is the only administrator account', 'wpshadow' );
			$threat_level  += 10;
		}

		$message = sprintf(
			/* translators: 1: user role */
			__( 'A user with the username "admin" exists (%s role). Attackers always try this username first. We recommend creating a new admin user with a unique username and deleting this one.', 'wpshadow' ),
			$is_admin ? __( 'Administrator', 'wpshadow' ) : ucfirst( $admin_user->roles[0] )
		);

		if ( ! empty( $risk_factors ) ) {
			$message .= ' ' . sprintf(
				/* translators: 1: list of risk factors */
				__( 'Additional risks: %s.', 'wpshadow' ),
				implode( ', ', $risk_factors )
			);
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $message,
			'severity'    => 'high',
			'threat_level' => min( $threat_level, 100 ),
			'auto_fixable' => false, // Cannot auto-fix (requires user action)
			'kb_link'     => 'https://wpshadow.com/kb/security-admin-username',
			'training_link' => 'https://wpshadow.com/training/security-admin-username',
			'manual_steps' => array(
				__( 'Create a new administrator user with a unique username', 'wpshadow' ),
				__( 'Log in as the new administrator', 'wpshadow' ),
				__( 'Delete the "admin" user account', 'wpshadow' ),
				__( 'Assign all posts/content to the new user when prompted', 'wpshadow' ),
			),
			'impact'      => array(
				'security' => __( 'Primary target for brute force attacks - attackers already know half your credentials', 'wpshadow' ),
			),
			'evidence'    => array(
				'username'    => 'admin',
				'user_id'     => $admin_user->ID,
				'role'        => implode( ', ', $admin_user->roles ),
				'email'       => $admin_user->user_email,
				'admin_count' => count( $admin_users ),
			),
		);
	}
}
