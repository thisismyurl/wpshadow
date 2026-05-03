<?php
/**
 * Treatment: Set Default User Role to Subscriber
 *
 * Updates the default_role option to 'subscriber'. Any role with more
 * capabilities than subscriber (editor, author, contributor) assigned as
 * the default registration role gives new self-registered users unnecessary
 * access to content, settings, or post editing.
 *
 * Risk level: safe — single option update, fully reversible.
 * Only affects new registrations — existing user roles are unchanged.
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
 * Sets the default user registration role to subscriber.
 */
class Treatment_Default_Role_Subscriber extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'default-role-subscriber';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Set default_role to 'subscriber'.
	 *
	 * @return array
	 */
	public static function apply() {
		$previous = get_option( 'default_role', 'subscriber' );
		update_option( 'thisismyurl_shadow_prev_default_role', $previous, false );
		update_option( 'default_role', 'subscriber' );

		return array(
			'success' => true,
			'message' => sprintf(
						/* translators: %s: previous role name. */
				__( 'Default registration role changed from "%s" to "subscriber". Existing users are unaffected.', 'thisismyurl-shadow' ),
				$previous
			),
			'details' => array(
				'previous_value' => $previous,
				'new_value'      => 'subscriber',
			),
		);
	}

	/**
	 * Restore the previous default_role value.
	 *
	 * @return array
	 */
	public static function undo() {
		$previous = get_option( 'thisismyurl_shadow_prev_default_role' );

		if ( false === $previous ) {
			return array(
				'success' => false,
				'message' => __( 'No previous value stored — nothing to restore.', 'thisismyurl-shadow' ),
			);
		}

		update_option( 'default_role', $previous );
		delete_option( 'thisismyurl_shadow_prev_default_role' );

		return array(
			'success' => true,
			'message' => sprintf(
						/* translators: %s: restored role name. */
				__( 'Default registration role restored to "%s".', 'thisismyurl-shadow' ),
				$previous
			),
		);
	}
}
