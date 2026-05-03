<?php
/**
 * Treatment: Set default new-user role to Subscriber
 *
 * The diagnostic fires when WordPress's "Default role" setting is anything
 * other than Subscriber. Contributor, Author, Editor, or Administrator as the
 * default role means every new account registration gains write access or
 * more. This treatment sets the default to "subscriber" and stores the
 * previous value for undo.
 *
 * Note: This is the diagnostic-linked counterpart to the
 * treatment-default-role-subscriber treatment which targets the older
 * diagnostic slug. Both fix the same underlying WordPress option.
 *
 * @package ThisIsMyURL\Shadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets the default WordPress new-user role to "subscriber".
 */
class Treatment_Default_User_Role extends Treatment_Base {

	/** @var string */
	protected static $slug = 'default-user-role';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Set default_role to 'subscriber'.
	 *
	 * @return array
	 */
	public static function apply(): array {
		$previous = (string) get_option( 'default_role', 'subscriber' );

		if ( 'subscriber' === $previous ) {
			return static::apply_option_with_backup(
				'default_role',
				'subscriber',
				'thisismyurl_shadow_default_user_role_prev',
				__( 'Default user role is already set to Subscriber. No changes made.', 'thisismyurl-shadow' ),
				__( 'Default user role is already set to Subscriber. No changes made.', 'thisismyurl-shadow' )
			);
		}

		return static::apply_option_with_backup(
			'default_role',
			'subscriber',
			'thisismyurl_shadow_default_user_role_prev',
			__( 'Default user role is already set to Subscriber. No changes made.', 'thisismyurl-shadow' ),
			sprintf(
				/* translators: %s: Previous role name */
				__( 'Default user role changed from "%s" to "Subscriber".', 'thisismyurl-shadow' ),
				esc_html( $previous )
			)
		);
	}

	/**
	 * Restore the previous default user role.
	 *
	 * @return array
	 */
	public static function undo(): array {
		return static::restore_option_from_backup(
			'default_role',
			'thisismyurl_shadow_default_user_role_prev',
			__( 'No stored previous role found.', 'thisismyurl-shadow' ),
			static function ( $prev_role ): string {
				return sprintf(
					/* translators: %s: Restored role name */
					__( 'Default user role restored to "%s".', 'thisismyurl-shadow' ),
					esc_html( (string) $prev_role )
				);
			}
		);
	}
}
