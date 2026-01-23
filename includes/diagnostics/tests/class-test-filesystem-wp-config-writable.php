<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: WP-Config Writable
 * Checks if wp-config.php is writable (security risk)
 */
class Test_Filesystem_WP_Config_Writable extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		$wp_config_path = ABSPATH . 'wp-config.php';

		if (file_exists($wp_config_path) && is_writable($wp_config_path)) {
			return array(
				'id'            => 'filesystem-wp-config-writable',
				'title'         => 'WP-Config File Writable',
				'threat_level'  => 75,
				'description'   => 'wp-config.php is writable by web server. This is a critical security risk.',
			);
		}

		return null;
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_wp_config_writable(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'WP-config is properly protected' : 'WP-config is writable (unsafe)',
		);
	}
}
