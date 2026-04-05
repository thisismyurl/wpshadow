<?php
/**
 * Treatment: Enable plugin auto-updates
 *
 * Enables WordPress plugin auto-updates for all currently installed standard
 * plugins by populating the `auto_update_plugins` option with their plugin
 * basenames.
 *
 * Undo restores the previous option state.
 *
 * @package WPShadow
 * @since   0.7056
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Plugin_Auto_Updates extends Treatment_Base {

	/** @var string */
	protected static $slug = 'plugin-auto-updates';

	private const BACKUP_OPTION = 'wpshadow_plugin_auto_updates_prev';

	public static function get_risk_level(): string {
		return 'moderate';
	}

	public static function apply(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		if ( ! is_array( $plugins ) ) {
			return array(
				'success' => false,
				'message' => __( 'Could not load the installed plugins list.', 'wpshadow' ),
			);
		}

		$plugin_files = array_values( array_filter( array_map( 'strval', array_keys( $plugins ) ) ) );
		if ( empty( $plugin_files ) ) {
			return array(
				'success' => true,
				'message' => __( 'No standard plugins are installed. No changes made.', 'wpshadow' ),
			);
		}

		$current = get_option( 'auto_update_plugins', '__wpshadow_option_missing__' );
		$target  = $plugin_files;

		if ( is_array( $current ) ) {
			$current_normalized = array_values( array_map( 'strval', $current ) );
			sort( $current_normalized );
			$target_normalized = $target;
			sort( $target_normalized );

			if ( $current_normalized === $target_normalized ) {
				return array(
					'success' => true,
					'message' => __( 'Plugin auto-updates are already enabled for all installed plugins. No changes made.', 'wpshadow' ),
				);
			}
		}

		static::save_backup_value(
			self::BACKUP_OPTION,
			array(
				'exists' => '__wpshadow_option_missing__' !== $current,
				'value'  => '__wpshadow_option_missing__' !== $current ? $current : null,
			)
		);

		update_option( 'auto_update_plugins', $target );

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: number of plugins with auto-updates enabled */
				__( 'Enabled plugin auto-updates for %d installed plugins.', 'wpshadow' ),
				count( $target )
			),
		);
	}

	public static function undo(): array {
		$loaded = static::load_backup_array( self::BACKUP_OPTION, array( 'exists', 'value' ), true );
		if ( ! $loaded['found'] ) {
			return array(
				'success' => false,
				'message' => __( 'No previous plugin auto-update setting was stored.', 'wpshadow' ),
			);
		}

		if ( ! empty( $loaded['value']['exists'] ) ) {
			update_option( 'auto_update_plugins', $loaded['value']['value'] );
		} else {
			delete_option( 'auto_update_plugins' );
		}

		return array(
			'success' => true,
			'message' => __( 'Plugin auto-update settings restored to the previous state.', 'wpshadow' ),
		);
	}
}