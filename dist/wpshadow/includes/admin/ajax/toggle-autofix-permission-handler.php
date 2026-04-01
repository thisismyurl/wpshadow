<?php

/**
 * Toggle Autofix Permission AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Options_Manager;
use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Toggle_Autofix_Permission_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hooks for auto-fix permission toggles.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_toggle_autofix_permission', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle auto-fix permission toggle requests.
	 *
	 * @since 0.6093.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_autofix_permission', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'key', '', true );
		$enabled    = self::get_post_param( 'enabled', 'bool', false );

		$permissions = Options_Manager::get_array( 'wpshadow_autofix_permissions', array() );

		if ( $enabled ) {
			$permissions[ $finding_id ] = true;
		} else {
			unset( $permissions[ $finding_id ] );
		}

		update_option( 'wpshadow_autofix_permissions', $permissions );

		// Log activity (#565: Activity Logging Expansion)
		Activity_Logger::log(
			'autofix_permission_' . ( $enabled ? 'enabled' : 'disabled' ),
			sprintf(
				__( 'Auto-fix %1$s for finding type: %2$s', 'wpshadow' ),
				$enabled ? __( 'enabled', 'wpshadow' ) : __( 'disabled', 'wpshadow' ),
				$finding_id
			),
			'workflows',
			array(
				'finding_id' => $finding_id,
				'enabled'    => (bool) $enabled,
			)
		);

		self::send_success(
			array(
				'message' => $enabled ? __( 'Auto-fix enabled for this type.', 'wpshadow' ) : __( 'Auto-fix disabled for this type.', 'wpshadow' ),
				'enabled' => (bool) $enabled,
			)
		);
	}
}
