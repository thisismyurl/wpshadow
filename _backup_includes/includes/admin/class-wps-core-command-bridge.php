<?php
/**
 * Bridge WPShadow features into WordPress core Command Palette.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport\Admin;

use WPShadow\CoreSupport\WPSHADOW_Feature_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers WPShadow features as commands in the core command palette.
 */
final class WPSHADOW_Core_Command_Bridge {
	/**
	 * Bootstrap the bridge.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		// Ensure commands load in the block editor where the core palette UI exists.
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Enqueue the bridge script and pass commands to JS.
	 *
	 * @return void
	 */
	public static function enqueue(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Avoid loading during JSON/AJAX requests where the command palette is not present.
		if ( wp_is_json_request() ) {
			return;
		}

		$commands = self::get_feature_commands();
		if ( empty( $commands ) ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-core-command-bridge',
			WPSHADOW_URL . 'assets/js/core-command-bridge.js',
			array( 'wp-commands', 'wp-url' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-core-command-bridge',
			'wpshadowCoreCommands',
			array(
				'commands' => $commands,
			)
		);
	}

	/**
	 * Build feature commands for the core palette.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function get_feature_commands(): array {
		$commands = array();

		if ( ! class_exists( WPSHADOW_Feature_Registry::class ) ) {
			return $commands;
		}

		$features = WPSHADOW_Feature_Registry::get_all_features();
		if ( empty( $features ) ) {
			return $commands;
		}

		foreach ( $features as $feature ) {
			$feature_id = isset( $feature['id'] ) ? sanitize_key( (string) $feature['id'] ) : '';
			if ( '' === $feature_id ) {
				continue;
			}

			// Determine enabled state; include disabled features with hint for discoverability.
			$enabled = isset( $feature['enabled'] ) ? (bool) $feature['enabled'] : (bool) ( $feature['default_enabled'] ?? true );

			$name        = isset( $feature['name'] ) ? (string) $feature['name'] : $feature_id;
			$description = isset( $feature['description'] ) ? (string) $feature['description'] : '';
			$hint        = $description;
			if ( ! $enabled ) {
				$hint = trim( $description . ' ' . __( '(Disabled — enable in WPShadow)', 'wpshadow' ) );
			}
			$icon        = isset( $feature['icon'] ) ? (string) $feature['icon'] : 'admin-generic';
			$aliases     = array();

			$feature_obj = WPSHADOW_Feature_Registry::get_feature_object( $feature_id );
			if ( $feature_obj && method_exists( $feature_obj, 'get_aliases' ) ) {
				$aliases = array_filter( array_map( 'strval', (array) $feature_obj->get_aliases() ) );
			}

			$commands[] = array(
				'id'          => 'wpshadow-feature-' . $feature_id,
				'label'       => $name,
				'description' => $description,
				'hint'        => $hint,
				'url'         => admin_url( 'admin.php?page=wpshadow-feature-details&feature=' . rawurlencode( $feature_id ) ),
				'icon'        => $icon,
				'keywords'    => $aliases,
				'category'    => 'WPShadow Features',
				'enabled'     => $enabled,
			);
		}

		return $commands;
	}
}
