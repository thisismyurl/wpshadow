<?php
/**
 * Wordfence Two-Factor Authentication Diagnostic
 *
 * Checks 2FA configuration for admin users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Two-Factor Auth Class
 *
 * Validates 2FA is enabled for administrators.
 *
 * @since 1.5029.1800
 */
class Diagnostic_Wordfence_2FA extends Diagnostic_Base {

	protected static $slug        = 'wordfence-2fa';
	protected static $title       = 'Wordfence Two-Factor Authentication';
	protected static $description = 'Checks 2FA configuration';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! class_exists( 'wordfence' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_wordfence_2fa';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Check if 2FA is available (Premium feature).
		$is_premium = wfConfig::get( 'isPaid', 0 );
		if ( ! $is_premium ) {
			return null; // 2FA is Premium-only.
		}

		// Get all administrator users.
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		$without_2fa = array();

		foreach ( $admin_users as $user ) {
			// Check if user has 2FA enabled.
			$has_2fa = get_user_meta( $user->ID, 'wf2faActivated', true );
			if ( ! $has_2fa ) {
				$without_2fa[] = array(
					'username' => $user->user_login,
					'email' => $user->user_email,
					'last_login' => get_user_meta( $user->ID, 'wf-last-login', true ),
				);
			}
		}

		if ( ! empty( $without_2fa ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d administrator(s) do not have two-factor authentication enabled. High security risk!', 'wpshadow' ),
					count( $without_2fa )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-wordfence-2fa',
				'data'         => array(
					'admins_without_2fa' => array_slice( $without_2fa, 0, 10 ),
					'total_affected' => count( $without_2fa ),
					'total_admins' => count( $admin_users ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
