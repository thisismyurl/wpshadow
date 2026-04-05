<?php
/**
 * Treatment: Enable WordPress core auto-updates
 *
 * WordPress minor core auto-updates provide background security patching. This
 * treatment enables option-driven core auto-updates when they have been turned
 * off in the database. If a wp-config constant disables updates, WPShadow will
 * not override it automatically.
 *
 * Undo: restores the previous auto_update_core_enabled option state.
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
 * Enables option-based core auto-updates.
 */
class Treatment_Auto_Update_Policy extends Treatment_Base {

	/** @var string */
	protected static $slug = 'auto-update-policy';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Enable core auto-updates when the site is not locked by constant.
	 *
	 * @return array
	 */
	public static function apply(): array {
		if ( defined( 'WP_AUTO_UPDATE_CORE' ) && false === WP_AUTO_UPDATE_CORE ) {
			return array(
				'success' => false,
				'message' => __( 'Core auto-updates are disabled by the WP_AUTO_UPDATE_CORE constant in wp-config.php. WPShadow will not override that automatically.', 'wpshadow' ),
			);
		}

		$exists   = null !== get_option( 'auto_update_core_enabled', null );
		$previous = $exists ? get_option( 'auto_update_core_enabled', null ) : null;

		static::save_backup_value(
			'wpshadow_auto_update_core_prev',
			array(
				'exists' => $exists,
				'value'  => $previous,
			)
		);

		update_option( 'auto_update_core_enabled', 1 );

		return array(
			'success' => true,
			'message' => __( 'WordPress core auto-updates enabled at the option level. Minor security releases can now install automatically.', 'wpshadow' ),
		);
	}

	/**
	 * Restore the previous core auto-update option state.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$loaded = static::load_backup_array( 'wpshadow_auto_update_core_prev', array( 'exists', 'value' ), true );

		if ( ! $loaded['found'] || ! is_array( $loaded['value'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'No previous core auto-update setting was stored.', 'wpshadow' ),
			);
		}

		$previous = $loaded['value'];
		if ( ! empty( $previous['exists'] ) ) {
			update_option( 'auto_update_core_enabled', $previous['value'] );
		} else {
			delete_option( 'auto_update_core_enabled' );
		}

		return array(
			'success' => true,
			'message' => __( 'Core auto-update setting restored to its previous state.', 'wpshadow' ),
		);
	}
}