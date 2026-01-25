<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Outdated Themes
 * Checks for installed themes with available updates
 */
class Test_Theme_Outdated_Themes extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		$updates = get_transient( 'update_themes' );

		if ( $updates && is_object( $updates ) && isset( $updates->response ) ) {
			$update_count = count( $updates->response );
			if ( $update_count > 0 ) {
				return array(
					'id'           => 'theme-outdated-themes',
					'title'        => 'Outdated Themes Available',
					'threat_level' => 30,
					'description'  => sprintf(
						'%d themes have available updates. Update to latest versions.',
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
	public static function test_live_outdated_themes(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'All themes are current' : 'Theme updates available',
		);
	}
}
