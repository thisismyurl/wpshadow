<?php
/**
 * Treatment: Close open user registration
 *
 * Open registration is often left enabled unintentionally on brochure or
 * business sites. This treatment disables public self-registration by setting
 * users_can_register to 0. Existing users and roles are unaffected.
 *
 * Undo: restores the previous users_can_register value.
 *
 * @package WPShadow
 * @since   0.7056.0300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disables public user registration.
 */
class Treatment_Registration_Setting_Intentional extends Treatment_Base {

	/** @var string */
	protected static $slug = 'registration-setting-intentional';

	/** @return string */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Disable users_can_register.
	 *
	 * @return array
	 */
	public static function apply(): array {
		$current = (string) get_option( 'users_can_register', '0' );

		if ( '0' === $current ) {
			return array(
				'success' => true,
				'message' => __( 'Open registration is already disabled. No changes made.', 'wpshadow' ),
			);
		}

		static::save_backup_value( 'wpshadow_users_can_register_prev', $current );
		update_option( 'users_can_register', '0' );

		return array(
			'success' => true,
			'message' => __( 'Open user registration has been disabled. Existing accounts are unaffected.', 'wpshadow' ),
		);
	}

	/**
	 * Restore the previous registration setting.
	 *
	 * @return array
	 */
	public static function undo(): array {
		return static::restore_option_from_backup(
			'users_can_register',
			'wpshadow_users_can_register_prev',
			__( 'No previous registration setting was stored.', 'wpshadow' ),
			static function ( $previous ): string {
				return '1' === (string) $previous
					? __( 'Open user registration restored to enabled.', 'wpshadow' )
					: __( 'Open user registration restored to disabled.', 'wpshadow' );
			}
		);
	}
}