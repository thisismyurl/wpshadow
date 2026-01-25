<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Outdated Plugins
 * Checks for installed plugins with available updates
 */
class Test_Plugin_Outdated_Plugins extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		if ( ! function_exists( 'get_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! function_exists( 'get_transient' ) ) {
			return null;
		}

		$plugins = get_plugins();
		$updates = get_transient( 'update_plugins' );

		if ( $updates && is_object( $updates ) && isset( $updates->response ) ) {
			$update_count = count( $updates->response );
			if ( $update_count > 0 ) {
				return array(
					'id'           => 'plugin-outdated-plugins',
					'title'        => 'Outdated Plugins Available',
					'threat_level' => 35,
					'description'  => sprintf(
						'%d plugins have available updates. Update to latest versions.',
						$update_count
					),
				);
			}
		}

		return null;
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_outdated_plugins(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'All plugins are current' : 'Plugin updates available',
		);
	}
}
