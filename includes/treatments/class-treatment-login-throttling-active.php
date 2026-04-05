<?php
/**
 * Treatment: Login Throttling Active
 *
 * Enables WPShadow's native login-throttling feature by toggling the
 * `wpshadow_login_throttling_enabled` option.
 *
 * The actual enforcement logic runs inside Treatment_Hooks::init() — it adds
 * `wp_login_failed` and `authenticate` hooks on every page load when the
 * option is true, so no file changes are required.
 *
 * Behaviour (managed by Treatment_Hooks):
 *  - After 5 failed logins from the same IP within 15 minutes, a 1-hour
 *    lockout transient is set for that IP.
 *  - The `authenticate` filter returns a WP_Error for locked-out IPs before
 *    any password hash comparison is attempted.
 *  - All thresholds are filterable via wpshadow_login_throttle_window,
 *    wpshadow_login_throttle_limit, and wpshadow_login_lockout_duration.
 *
 * Risk level: safe — option toggle only.
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enables native brute-force login protection via a simple option toggle.
 */
class Treatment_Login_Throttling_Active extends Treatment_Base {

	/** @var string */
	protected static $slug = 'login-throttling-active';

	const OPTION_KEY = 'wpshadow_login_throttling_enabled';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Enable native login throttling.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		update_option( self::OPTION_KEY, true, false );

		return [
			'success' => true,
			'message' => __(
				'Login throttling enabled. After 5 failed attempts from the same IP within 15 minutes, that IP is locked out for 1 hour. All thresholds are filterable. '
				. 'This protection is WPShadow-native and works alongside any existing security plugins.',
				'wpshadow'
			),
		];
	}

	/**
	 * Disable native login throttling, restoring WordPress defaults (unlimited attempts).
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		delete_option( self::OPTION_KEY );

		return [
			'success' => true,
			'message' => __( 'Login throttling disabled. WordPress will no longer limit repeated login attempts via this feature. Consider installing a dedicated security plugin for continued protection.', 'wpshadow' ),
		];
	}
}
