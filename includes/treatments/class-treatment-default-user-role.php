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
 * @package WPShadow
 * @since   0.6093.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

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
		$prev_role = get_option( 'default_role', 'subscriber' );

		if ( 'subscriber' === $prev_role ) {
			return array(
				'success' => true,
				'message' => __( 'Default user role is already set to Subscriber. No changes made.', 'wpshadow' ),
			);
		}

		update_option( 'wpshadow_default_user_role_prev', $prev_role, false );
		update_option( 'default_role', 'subscriber' );

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: Previous role name */
				__( 'Default user role changed from "%s" to "Subscriber".', 'wpshadow' ),
				esc_html( $prev_role )
			),
		);
	}

	/**
	 * Restore the previous default user role.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$prev_role = get_option( 'wpshadow_default_user_role_prev' );

		if ( false === $prev_role ) {
			return array(
				'success' => false,
				'message' => __( 'No stored previous role found.', 'wpshadow' ),
			);
		}

		update_option( 'default_role', $prev_role );
		delete_option( 'wpshadow_default_user_role_prev' );

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: Restored role name */
				__( 'Default user role restored to "%s".', 'wpshadow' ),
				esc_html( $prev_role )
			),
		);
	}
}
