<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Head Cleanup - Emoji Detection Scripts
 * Checks if WordPress emoji detection scripts are enabled and can be removed
 */
class Test_Performance_Head_Cleanup_Emoji extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		if (!has_action('wp_head', 'print_emoji_detection_script') && 
		    !has_action('admin_print_scripts', 'print_emoji_detection_script')) {
			return null;
		}

		return array(
			'id'            => 'head-cleanup-emoji',
			'title'         => 'Emoji Detection Scripts Enabled',
			'threat_level'  => 15,
			'description'   => 'Emoji detection scripts load on every page but are rarely needed. Removing them reduces requests and improves performance.',
		);
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_head_cleanup_emoji(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Emoji detection is not enabled' : 'Emoji detection scripts found',
		);
	}
}
