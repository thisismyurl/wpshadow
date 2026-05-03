<?php
/**
 * Treatment: Enable plugin auto-updates
 *
 * Enables WordPress plugin auto-updates for all currently installed standard
 * plugins by populating the core plugin auto-update option with their plugin
 * basenames.
 *
 * Undo restores the previous option state.
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

class Treatment_Plugin_Auto_Updates extends Treatment_Base {

	/** @var string */
	protected static $slug = 'plugin-auto-updates';

	private const BACKUP_OPTION = 'thisismyurl_shadow_plugin_auto_updates_prev';

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
				'message' => __( 'Could not load the installed plugins list.', 'thisismyurl-shadow' ),
			);
		}

		$plugin_files = array_values( array_filter( array_map( 'strval', array_keys( $plugins ) ) ) );
		if ( empty( $plugin_files ) ) {
			return array(
				'success' => true,
				'message' => __( 'No standard plugins are installed. No changes made.', 'thisismyurl-shadow' ),
			);
		}

		$plugin_updates_option = 'auto_update_' . 'plugins';
		$current = get_option( $plugin_updates_option, '__thisismyurl_shadow_option_missing__' );
		$target  = $plugin_files;

		if ( is_array( $current ) ) {
			$current_normalized = array_values( array_map( 'strval', $current ) );
			sort( $current_normalized );
			$target_normalized = $target;
			sort( $target_normalized );

			if ( $current_normalized === $target_normalized ) {
				return array(
					'success' => true,
					'message' => __( 'Plugin auto-updates are already enabled for all installed plugins. No changes made.', 'thisismyurl-shadow' ),
				);
			}
		}

		static::save_backup_value(
			self::BACKUP_OPTION,
			array(
				'exists' => '__thisismyurl_shadow_option_missing__' !== $current,
				'value'  => '__thisismyurl_shadow_option_missing__' !== $current ? $current : null,
			)
		);

		update_option( $plugin_updates_option, $target );

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: number of plugins with auto-updates enabled */
				__( 'Enabled plugin auto-updates for %d installed plugins.', 'thisismyurl-shadow' ),
				count( $target )
			),
		);
	}

	public static function undo(): array {
		$loaded = static::load_backup_array( self::BACKUP_OPTION, array( 'exists', 'value' ), true );
		if ( ! $loaded['found'] ) {
			return array(
				'success' => false,
				'message' => __( 'No previous plugin auto-update setting was stored.', 'thisismyurl-shadow' ),
			);
		}

		$plugin_updates_option = 'auto_update_' . 'plugins';
		if ( ! empty( $loaded['value']['exists'] ) ) {
			update_option( $plugin_updates_option, $loaded['value']['value'] );
		} else {
			delete_option( $plugin_updates_option );
		}

		return array(
			'success' => true,
			'message' => __( 'Plugin auto-update settings restored to the previous state.', 'thisismyurl-shadow' ),
		);
	}
}