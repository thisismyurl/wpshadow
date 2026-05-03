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
 * @package ThisIsMyURL\Shadow
 * @since   0.7056
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

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
				'message' => __( 'Open registration is already disabled. No changes made.', 'thisismyurl-shadow' ),
			);
		}

		static::save_backup_value( 'thisismyurl_shadow_users_can_register_prev', $current );
		update_option( 'users_can_register', '0' );

		return array(
			'success' => true,
			'message' => __( 'Open user registration has been disabled. Existing accounts are unaffected.', 'thisismyurl-shadow' ),
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
			'thisismyurl_shadow_users_can_register_prev',
			__( 'No previous registration setting was stored.', 'thisismyurl-shadow' ),
			static function ( $previous ): string {
				return '1' === (string) $previous
					? __( 'Open user registration restored to enabled.', 'thisismyurl-shadow' )
					: __( 'Open user registration restored to disabled.', 'thisismyurl-shadow' );
			}
		);
	}
}